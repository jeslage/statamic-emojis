<?php

namespace StatamicEmojis;

use StatamicEmojis\Contracts\SetNaming;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Statamic\Fields\Value;

class EmojiSets
{
    public const NATIVE = 'native';

    public const VARIATION_SELECTOR = 'fe0f';

    // Subdivision flags (🏴 + tag characters) must come before the general case, which would match the 🏴 alone.
    public const EMOJI_PATTERN = '/\p{Regional_Indicator}{2}'
        .'|[#*0-9]\x{FE0F}?\x{20E3}'
        .'|\x{1F3F4}[\x{E0061}-\x{E007A}]+\x{E007F}'
        .'|\p{Extended_Pictographic}(?:\x{FE0F}|\p{Emoji_Modifier})*(?:\x{200D}\p{Extended_Pictographic}(?:\x{FE0F}|\p{Emoji_Modifier})*)*/u';

    public const BUILT_IN = [
        'noto' => [
            'label' => 'statamic-emojis::messages.sets.noto',
            'url' => 'https://cdn.jsdelivr.net/gh/googlefonts/noto-emoji@e20cbc2bbec1926686be9f9bee7d1d2cfa1fea0e',
            'naming' => 'noto',
        ],
        'fluent' => [
            'label' => 'statamic-emojis::messages.sets.fluent',
            'url' => 'https://cdn.jsdelivr.net/npm/@lobehub/fluent-emoji-flat@1.1.0/assets',
            'naming' => 'fluent',
        ],
        'openmoji' => [
            'label' => 'statamic-emojis::messages.sets.openmoji',
            'url' => 'https://cdn.jsdelivr.net/npm/openmoji@17.0.0/color/svg',
            'naming' => 'openmoji',
        ],
    ];

    public const NAME_PATTERN = '/^[a-z]+$/';

    public const CODES_KEY = 'statamic-emojis:sets:codes';

    // Uncached images the image route fetches per visitor and minute.
    public const FETCH_LIMIT = 60;

    protected static array $extended = [];

    protected ?array $codeIndex = null;

    public static function extend(string $name, array $definition): void
    {
        static::$extended[$name] = array_merge(static::$extended[$name] ?? [], $definition);
    }

    public static function flushExtended(): void
    {
        static::$extended = [];
    }

    /** @return array<string, array{label?: string, url: string, naming?: string}> */
    public function definitions(): array
    {
        if (! app(EmojiSettings::class)->pro()) {
            return [];
        }

        $definitions = self::BUILT_IN;

        foreach ([config('statamic-emojis.sets', []), static::$extended] as $additions) {
            foreach ((array) $additions as $name => $definition) {
                $definitions[$name] = array_merge($definitions[$name] ?? [], (array) $definition);
            }
        }

        $disabled = (array) config('statamic-emojis.disabled_sets', []);

        return collect($definitions)
            ->reject(fn ($definition, $name) => in_array($name, $disabled, true) || in_array('*', $disabled, true))
            ->filter(fn ($definition, $name) => preg_match(self::NAME_PATTERN, $name) && $name !== self::NATIVE && ! empty($definition['url']))
            ->all();
    }

    public function definition(string $set): ?array
    {
        return $this->definitions()[$set] ?? null;
    }

    public function sets(): array
    {
        return [self::NATIVE, ...array_keys($this->definitions())];
    }

    public function isImageSet(?string $set): bool
    {
        return $set !== null && $set !== self::NATIVE && $this->definition($set) !== null;
    }

    // $translate = false for blueprints built before the user's locale is known (the CP translates them)
    public function label(string $set, bool $translate = true): string
    {
        $label = $set === self::NATIVE
            ? 'statamic-emojis::messages.sets.native'
            : ($this->definition($set)['label'] ?? Str::headline($set));

        return $translate ? __($label) : $label;
    }

    public function default(): string
    {
        $set = app(EmojiSettings::class)->set();

        return $this->isImageSet($set) ? $set : self::NATIVE;
    }

    public function resolve(?string $set): string
    {
        if ($set === self::NATIVE || $this->isImageSet($set)) {
            return $set;
        }

        return $this->default();
    }

    public function codepoints(string $emoji): string
    {
        return collect(mb_str_split($emoji))
            ->map(fn ($char) => dechex(mb_ord($char)))
            ->implode('-');
    }

    // Sets name their files differently: try fully qualified, then without a trailing FE0F, then without any FE0F.
    public function candidates(string $set, string $codes): array
    {
        $parts = explode('-', strtolower($codes));
        $variants = collect([
            $parts,
            end($parts) === self::VARIATION_SELECTOR ? array_slice($parts, 0, -1) : null,
            array_values(array_diff($parts, [self::VARIATION_SELECTOR])),
        ])->filter()->map(fn ($p) => array_values($p))->unique(fn ($p) => implode('-', $p))->values();

        $naming = $this->definition($set)['naming'] ?? 'fluent';
        $custom = class_exists($naming) && is_subclass_of($naming, SetNaming::class) ? app($naming) : null;

        if ($custom !== null) {
            return $variants->flatMap(fn ($p) => $custom->files($p))->unique()->values()->all();
        }

        // The built-in sets pad codepoints to at least four digits (1️⃣ = 0031-fe0f-20e3). Sets added with the
        // same naming may not (Twemoji: 31-20e3), so the unpadded names follow as a fallback.
        $padded = $variants->map(fn ($p) => array_map(fn ($code) => str_pad($code, 4, '0', STR_PAD_LEFT), $p));

        return $padded->concat($variants)->flatMap(fn ($p) => match ($naming) {
            'noto' => $this->isFlag($p)
                ? ['third_party/region-flags/waved-svg/emoji_u'.implode('_', $p).'.svg']
                : ['2D/svg/emoji_u'.implode('_', $p).'.svg'],
            'openmoji' => [strtoupper(implode('-', $p)).'.svg'],
            default => [implode('-', $p).'.svg'],
        })->unique()->values()->all();
    }

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

        $base = rtrim($this->definition($set)['url'], '/');

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

    public function isCached(string $set, string $codes): bool
    {
        return File::exists($this->cachePath($set, strtolower($codes)));
    }

    protected function store(string $set, string $codes, string $svg): void
    {
        File::ensureDirectoryExists(dirname($path = $this->cachePath($set, $codes)));
        File::put($path, $svg);
    }

    public function warm(string $set, array $codesList): array
    {
        $base = rtrim($this->definition($set)['url'], '/');
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

    public function fallbackSvg(string $codes): string
    {
        $emoji = collect(explode('-', $codes))->map(fn ($hex) => mb_chr(hexdec($hex)))->implode('');

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36 36">'
            .'<text x="50%" y="50%" font-size="30" text-anchor="middle" dominant-baseline="central">'
            .e($emoji).'</text></svg>';
    }

    public function url(string $set, string $emoji): string
    {
        return $this->baseUrl()."/{$set}/".$this->codepoints($emoji).'.svg';
    }

    public function baseUrl(): string
    {
        return rtrim(url('/!/statamic-emojis/sets'), '/');
    }

    public function replace(?string $html, string $set): string
    {
        $html = (string) $html;
        $imageSet = $this->isImageSet($set);
        $custom = app(CustomEmojis::class);

        if ($html === '' || (! $imageSet && ! str_contains($html, ':'))) {
            return $html;
        }

        $parts = preg_split('/(<[^>]*>)/u', $html, -1, PREG_SPLIT_DELIM_CAPTURE);

        return collect($parts)->map(function ($part) use ($set, $imageSet, $custom) {
            if ($part === '' || str_starts_with($part, '<')) {
                return $part;
            }

            $part = $custom->replace($part);

            if (! $imageSet) {
                return $part;
            }

            // Split again so the <img> tags just inserted for custom emojis stay untouched
            return collect(preg_split('/(<[^>]*>)/u', $part, -1, PREG_SPLIT_DELIM_CAPTURE))
                ->map(fn ($text) => $text === '' || str_starts_with($text, '<')
                    ? $text
                    : preg_replace_callback(self::EMOJI_PATTERN, fn ($m) => $this->img($set, $m[0]), $text))
                ->implode('');
        })->implode('');
    }

    public function html(mixed $value, ?string $set = null): string
    {
        if ($value instanceof Value) {
            $value = $value->value();
        }

        if (! is_string($value) && ! (is_object($value) && method_exists($value, '__toString'))) {
            return '';
        }

        return $this->replace((string) $value, $this->resolve($set));
    }

    public function img(string $set, string $emoji): string
    {
        return sprintf(
            '<img class="emoji" src="%s" alt="%s" width="20" height="20" draggable="false" loading="lazy" decoding="async">',
            e($this->url($set, $emoji)),
            e($emoji),
        );
    }

    // Country flags (two regional indicators) and subdivision flags (🏴 + tag characters)
    protected function isFlag(array $parts): bool
    {
        if (($parts[0] ?? null) === '1f3f4' && count($parts) > 2 && end($parts) === 'e007f') {
            return true;
        }

        return count($parts) === 2
            && collect($parts)->every(fn ($p) => hexdec($p) >= 0x1F1E6 && hexdec($p) <= 0x1F1FF);
    }

    public function allCodes(Emojibase $emojibase): array
    {
        return collect($emojibase->data('en'))
            ->flatMap(fn ($emoji) => [$emoji['emoji'] ?? null, ...array_column($emoji['skins'] ?? [], 'emoji')])
            ->filter()
            ->map(fn ($emoji) => $this->codepoints($emoji))
            ->unique()
            ->values()
            ->all();
    }

    // The Emojibase form of a requested code, or null for codes Emojibase doesn't know. FE0F and leading zeros
    // are ignored, so every spelling of an emoji maps to one code (and one cached file).
    public function canonical(string $codes): ?string
    {
        try {
            $this->codeIndex ??= Cache::remember(
                self::CODES_KEY,
                config('statamic-emojis.emojibase.cache_ttl'),
                fn () => collect($this->allCodes(app(Emojibase::class)))
                    ->mapWithKeys(fn ($code) => [$this->stripped($code) => $code])
                    ->all(),
            );
        } catch (\Throwable) {
            // Without Emojibase data nothing can be checked, so nothing is fetched.
            return null;
        }

        return $this->codeIndex[$this->stripped($codes)] ?? null;
    }

    protected function stripped(string $codes): string
    {
        return collect(explode('-', strtolower($codes)))
            ->reject(fn ($code) => $code === self::VARIATION_SELECTOR)
            ->map(fn ($code) => ltrim($code, '0') ?: '0')
            ->implode('-');
    }

    public function cacheStatus(): array
    {
        try {
            $total = count($this->allCodes(app(Emojibase::class)));
        } catch (\Throwable) {
            $total = null;
        }

        return collect($this->sets())
            ->reject(fn ($set) => $set === self::NATIVE)
            ->map(function ($set) use ($total) {
                $directory = rtrim(config('statamic-emojis.images.cache_path'), '/')."/{$set}";
                $files = File::isDirectory($directory) ? File::files($directory) : [];

                return [
                    'set' => $set,
                    'label' => $this->label($set),
                    'cached' => count($files),
                    'size' => array_sum(array_map(fn ($file) => $file->getSize(), $files)),
                    'total' => $total,
                ];
            })
            ->values()
            ->all();
    }

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
