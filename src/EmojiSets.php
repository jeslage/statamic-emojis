<?php

namespace Convoy\Emojis;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

/**
 * Emoji image sets (Noto, Fluent, OpenMoji) besides the device's native emoji font.
 *
 * Images are fetched once from the configured upstream, stored in the cache path and
 * served by the addon (see EmojiImageController), so browsers never talk to the CDN.
 */
class EmojiSets
{
    public const NATIVE = 'native';

    public const VARIATION_SELECTOR = 'fe0f';

    /** Matches a single emoji: flags, keycaps, and (ZWJ) sequences with modifiers. */
    public const EMOJI_PATTERN = '/\p{Regional_Indicator}{2}'
        .'|[#*0-9]\x{FE0F}?\x{20E3}'
        .'|\p{Extended_Pictographic}(?:\x{FE0F}|\p{Emoji_Modifier})*(?:\x{200D}\p{Extended_Pictographic}(?:\x{FE0F}|\p{Emoji_Modifier})*)*'
        .'|\x{1F3F4}[\x{E0061}-\x{E007A}]+\x{E007F}/u';

    /** All selectable sets: native plus the configured image sets. */
    public function sets(): array
    {
        return [self::NATIVE, ...array_keys(config('statamic-emojis.sets', []))];
    }

    public function isImageSet(?string $set): bool
    {
        return $set !== null && $set !== self::NATIVE && array_key_exists($set, config('statamic-emojis.sets', []));
    }

    /** The site-wide set from the config, "native" if it isn't a known set. */
    public function default(): string
    {
        $set = config('statamic-emojis.set', self::NATIVE);

        return $this->isImageSet($set) ? $set : self::NATIVE;
    }

    /** A field's set override if valid, otherwise the site-wide set. */
    public function resolve(?string $set): string
    {
        if ($set === self::NATIVE || $this->isImageSet($set)) {
            return $set;
        }

        return $this->default();
    }

    /** Lowercase hex codepoints of an emoji joined by "-", fully qualified (keeps FE0F). */
    public function codepoints(string $emoji): string
    {
        return collect(mb_str_split($emoji))
            ->map(fn ($char) => dechex(mb_ord($char)))
            ->implode('-');
    }

    /**
     * Upstream file paths to try for an emoji, in order. The sets name their files differently,
     * so try the fully qualified form, then without a trailing FE0F, then without any FE0F.
     */
    public function candidates(string $set, string $codes): array
    {
        $parts = explode('-', strtolower($codes));
        $variants = collect([
            $parts,
            end($parts) === self::VARIATION_SELECTOR ? array_slice($parts, 0, -1) : null,
            array_values(array_diff($parts, [self::VARIATION_SELECTOR])),
        ])->filter()->map(fn ($p) => array_values($p))->unique(fn ($p) => implode('-', $p))->values();

        $naming = config("statamic-emojis.sets.{$set}.naming");

        return $variants->flatMap(fn ($p) => match ($naming) {
            'noto' => $this->isFlag($p)
                ? ['third_party/region-flags/waved-svg/emoji_u'.implode('_', $p).'.svg']
                : ['2D/svg/emoji_u'.implode('_', $p).'.svg'],
            'openmoji' => [strtoupper(implode('-', $p)).'.svg'],
            default => [implode('-', $p).'.svg'],
        })->unique()->values()->all();
    }

    /** The SVG of an emoji in a set, or null if the set has none. */
    public function svg(string $set, string $codes): ?string
    {
        if (! $this->isImageSet($set)) {
            return null;
        }

        $codes = strtolower($codes);
        $path = $this->cachePath($set, $codes);

        if (File::exists($path)) {
            return File::get($path);
        }

        $missKey = $this->missKey($set, $codes);

        if (Cache::has($missKey)) {
            return null;
        }

        $base = rtrim(config("statamic-emojis.sets.{$set}.url"), '/');

        foreach ($this->candidates($set, $codes) as $file) {
            try {
                $response = Http::timeout(10)->get("{$base}/{$file}");
            } catch (ConnectionException) {
                // Network problem, not a gap in the set: don't remember it as a miss, retry next time.
                return null;
            }

            if ($response->successful() && str_contains($response->body(), '<svg')) {
                $this->store($set, $codes, $response->body());

                return $response->body();
            }
        }

        Cache::put($missKey, true, now()->addDay());

        return null;
    }

    /** Whether an emoji's image is in the cache already. */
    public function isCached(string $set, string $codes): bool
    {
        return File::exists($this->cachePath($set, strtolower($codes)));
    }

    protected function store(string $set, string $codes, string $svg): void
    {
        File::ensureDirectoryExists(dirname($path = $this->cachePath($set, $codes)));
        File::put($path, $svg);
    }

    /**
     * Fetch several emojis at once (used by the emojis:warm command). Tries each emoji's first
     * candidate concurrently, then the remaining candidates one by one.
     * Returns the codes that couldn't be fetched (not in the set, or a network error).
     */
    public function warm(string $set, array $codesList): array
    {
        $base = rtrim(config("statamic-emojis.sets.{$set}.url"), '/');
        $todo = collect($codesList)->map(fn ($c) => strtolower($c))
            ->reject(fn ($codes) => $this->isCached($set, $codes))
            ->values();

        // Failed requests come back as exceptions in the pool results instead of being thrown.
        $responses = Http::pool(fn ($pool) => $todo->map(
            fn ($codes) => $pool->as($codes)->timeout(15)->get("{$base}/".$this->candidates($set, $codes)[0])
        )->all());

        return $todo->reject(function ($codes) use ($set, $responses) {
            $response = $responses[$codes] ?? null;

            if ($response instanceof Response && $response->successful() && str_contains($response->body(), '<svg')) {
                $this->store($set, $codes, $response->body());

                return true;
            }

            return $this->svg($set, $codes) !== null;
        })->values()->all();
    }

    /** Fallback SVG for emojis a set doesn't have: the native character as text. */
    public function fallbackSvg(string $codes): string
    {
        $emoji = collect(explode('-', $codes))->map(fn ($hex) => mb_chr(hexdec($hex)))->implode('');

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36 36">'
            .'<text x="50%" y="50%" font-size="30" text-anchor="middle" dominant-baseline="central">'
            .e($emoji).'</text></svg>';
    }

    /** Public URL of an emoji's image in a set. */
    public function url(string $set, string $emoji): string
    {
        return $this->baseUrl()."/{$set}/".$this->codepoints($emoji).'.svg';
    }

    /** Base URL of the image route, without trailing slash: "{baseUrl}/{set}/{codes}.svg". */
    public function baseUrl(): string
    {
        return rtrim(url('/!/statamic-emojis/sets'), '/');
    }

    /**
     * Replace every emoji in text or HTML with an <img> of the set. Only text between tags is
     * touched, so attributes stay intact. Native returns the input unchanged.
     */
    public function replace(?string $html, string $set): string
    {
        $html = (string) $html;

        if (! $this->isImageSet($set) || $html === '') {
            return $html;
        }

        $parts = preg_split('/(<[^>]*>)/u', $html, -1, PREG_SPLIT_DELIM_CAPTURE);

        return collect($parts)->map(function ($part) use ($set) {
            if ($part === '' || str_starts_with($part, '<')) {
                return $part;
            }

            return preg_replace_callback(self::EMOJI_PATTERN, fn ($m) => $this->img($set, $m[0]), $part);
        })->implode('');
    }

    public function img(string $set, string $emoji): string
    {
        return sprintf(
            '<img class="emoji" src="%s" alt="%s" width="20" height="20" draggable="false" loading="lazy" decoding="async">',
            e($this->url($set, $emoji)),
            e($emoji),
        );
    }

    protected function isFlag(array $parts): bool
    {
        return count($parts) === 2
            && collect($parts)->every(fn ($p) => hexdec($p) >= 0x1F1E6 && hexdec($p) <= 0x1F1FF);
    }

    /**
     * Delete a set's cached images and forget which emojis it was missing.
     * Returns the number of deleted images.
     */
    public function clear(string $set): int
    {
        $directory = rtrim(config('statamic-emojis.images.cache_path'), '/')."/{$set}";
        $count = File::isDirectory($directory) ? count(File::files($directory)) : 0;

        File::deleteDirectory($directory);

        // Miss notes are individual cache entries; bumping the set's generation invalidates all at once.
        Cache::forever($this->generationKey($set), $this->generation($set) + 1);

        return $count;
    }

    protected function missKey(string $set, string $codes): string
    {
        return "statamic-emojis:sets:miss:{$set}:{$this->generation($set)}:{$codes}";
    }

    protected function generation(string $set): int
    {
        return (int) Cache::get($this->generationKey($set), 0);
    }

    protected function generationKey(string $set): string
    {
        return "statamic-emojis:sets:generation:{$set}";
    }

    protected function cachePath(string $set, string $codes): string
    {
        return rtrim(config('statamic-emojis.images.cache_path'), '/')."/{$set}/{$codes}.svg";
    }
}
