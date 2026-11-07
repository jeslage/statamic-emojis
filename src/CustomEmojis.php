<?php

namespace StatamicEmojis;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Assets\AssetContainer;
use Statamic\Facades\AssetContainer as AssetContainers;

class CustomEmojis
{
    public const SHORTCODE_PATTERN = '/:([a-z0-9_+-]+):/';

    protected const CACHE_KEY = 'statamic-emojis:custom';

    public function isEnabled(): bool
    {
        return app(EmojiSettings::class)->customEmojis() && $this->container() !== null;
    }

    public function containerHandle(): string
    {
        return config('statamic-emojis.custom.container', 'statamic-emojis');
    }

    public function container(): ?AssetContainer
    {
        return AssetContainers::find($this->containerHandle());
    }

    /** @return Collection<string, array> */
    public function all(): Collection
    {
        if (! $this->isEnabled()) {
            return collect();
        }

        return collect(Cache::rememberForever(self::CACHE_KEY, fn () => $this->load()));
    }

    public function find(?string $shortcode): ?array
    {
        return $this->all()->get(trim((string) $shortcode, ':'));
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function owns(Asset $asset): bool
    {
        return $asset->container()?->handle() === $this->containerHandle();
    }

    public static function shortcodeFor(Asset $asset): string
    {
        return static::normalize($asset->get('shortcode') ?: $asset->filename());
    }

    public static function normalize(string $value): string
    {
        return trim(preg_replace('/[^a-z0-9_+-]+/', '-', Str::lower(Str::ascii(trim($value, ': ')))), '-');
    }

    public function replace(string $text): string
    {
        if (! str_contains($text, ':') || ! $this->isEnabled()) {
            return $text;
        }

        return preg_replace_callback(self::SHORTCODE_PATTERN, function ($match) {
            $emoji = $this->find($match[1]);

            return $emoji ? $this->img($emoji) : $match[0];
        }, $text);
    }

    public function img(array $emoji): string
    {
        return sprintf(
            '<img class="emoji emoji-custom" src="%s" alt="%s" title=":%s:" width="20" height="20" draggable="false" loading="lazy" decoding="async">',
            e($emoji['url']),
            e($emoji['label']),
            e($emoji['shortcode']),
        );
    }

    protected function load(): array
    {
        return $this->container()
            ->queryAssets()
            ->get()
            ->filter(fn (Asset $asset) => $asset->isImage() || $asset->extension() === 'svg')
            ->sortBy(fn (Asset $asset) => $asset->path())
            ->map(fn (Asset $asset) => [
                'shortcode' => $shortcode = static::shortcodeFor($asset),
                'label' => $asset->get('alt') ?: Str::of($shortcode)->replace(['-', '_'], ' ')->ucfirst()->toString(),
                'keywords' => array_values(array_filter((array) $asset->get('keywords'))),
                'url' => $asset->url(),
            ])
            ->filter(fn ($emoji) => $emoji['shortcode'] !== '')
            ->unique('shortcode')
            ->keyBy('shortcode')
            ->all();
    }
}
