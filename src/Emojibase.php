<?php

namespace StatamicEmojis;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class Emojibase
{
    // Keep in sync with resources/js/locale.js.
    public const LOCALES = [
        'bn', 'da', 'de', 'en-gb', 'en', 'es-mx', 'es', 'et', 'fi', 'fr', 'hi', 'hu', 'it', 'ja',
        'ko', 'lt', 'ms', 'nb', 'nl', 'pl', 'pt', 'ru', 'sv', 'th', 'uk', 'vi', 'zh-hant', 'zh',
    ];

    // Seconds a failed fetch is remembered before it is tried again.
    public const FAILURE_TTL = 60;

    public const SKIN_TONES = ['none', 'light', 'medium-light', 'medium', 'medium-dark', 'dark'];

    // Emojibase group indexes; group 2 (skin tone and hair components) is never shown.
    public const CATEGORIES = [
        'smileys-people' => [0, 1],
        'animals-nature' => [3],
        'food-drink' => [4],
        'travel-places' => [5],
        'activities' => [6],
        'objects' => [7],
        'symbols' => [8],
        'flags' => [9],
    ];

    protected array $labels = [];

    // Same as emojibaseLocale() in resources/js/locale.js.
    public static function locale(?string $locale): string
    {
        $normalized = str_replace('_', '-', strtolower((string) ($locale ?: 'en')));

        if (in_array($normalized, ['zh-tw', 'zh-hk', 'zh-mo', 'zh-hant'], true)) {
            return 'zh-hant';
        }

        if (self::isLocale($normalized)) {
            return $normalized;
        }

        $language = explode('-', $normalized)[0];

        if (in_array($language, ['no', 'nn'], true)) {
            return 'nb';
        }

        return self::isLocale($language) ? $language : 'en';
    }

    public function labels(string $locale): array
    {
        $locale = self::locale($locale);

        return $this->labels[$locale] ??= collect($this->data($locale))
            ->flatMap(fn ($emoji) => [
                $emoji['emoji'] ?? '' => $emoji['label'] ?? null,
                ...collect($emoji['skins'] ?? [])->mapWithKeys(fn ($skin) => [$skin['emoji'] ?? '' => $skin['label'] ?? null])->all(),
            ])
            ->filter()
            ->all();
    }

    public function data(string $locale, array $categories = []): array
    {
        $emojis = $this->fetch($locale, 'data.json');

        $groups = collect($categories)
            ->flatMap(fn ($key) => self::CATEGORIES[$key] ?? [])
            ->unique()
            ->values()
            ->all();

        if (empty($groups)) {
            return $emojis;
        }

        return array_values(array_filter(
            $emojis,
            fn ($emoji) => isset($emoji['group']) && in_array($emoji['group'], $groups, true)
        ));
    }

    public function messages(string $locale): array
    {
        return $this->fetch($locale, 'messages.json');
    }

    public static function isLocale(string $locale): bool
    {
        return in_array($locale, self::LOCALES, true);
    }

    public const SHORTCODES = 'shortcodes/emojibase.json';

    // English for every locale, as shortcodes are typed rather than read.
    public function shortcodes(): array
    {
        return $this->fetch('en', self::SHORTCODES);
    }

    public function clear(): void
    {
        foreach (self::LOCALES as $locale) {
            foreach (['data.json', 'messages.json'] as $file) {
                $this->forget($this->cacheKey($locale, $file));
            }
        }

        $this->forget($this->cacheKey('en', self::SHORTCODES));

        Cache::forget(EmojiSets::CODES_KEY);
    }

    protected function forget(string $key): void
    {
        Cache::forget($key);
        Cache::forget("{$key}:failed");
    }

    protected function cacheKey(string $locale, string $file): string
    {
        return 'statamic-emojis:emojibase:'.md5($this->url($locale, $file));
    }

    protected function url(string $locale, string $file): string
    {
        return rtrim(config('statamic-emojis.emojibase.url'), '/')."/{$locale}/{$file}";
    }

    protected function fetch(string $locale, string $file): array
    {
        $url = $this->url($locale, $file);
        $key = $this->cacheKey($locale, $file);

        if (($data = Cache::get($key)) !== null) {
            return $data;
        }

        // Remember a failed fetch briefly, so pages don't each wait for the timeout while the CDN is down.
        if (Cache::has("{$key}:failed")) {
            throw new RuntimeException("Fetching {$url} failed recently, retrying later.");
        }

        try {
            $data = Http::timeout(15)->get($url)->throw()->json();
        } catch (Throwable $e) {
            Cache::put("{$key}:failed", true, self::FAILURE_TTL);

            throw $e;
        }

        if (! is_array($data)) {
            Cache::put("{$key}:failed", true, self::FAILURE_TTL);

            throw new RuntimeException("{$url} did not return JSON.");
        }

        Cache::put($key, $data, config('statamic-emojis.emojibase.cache_ttl'));

        return $data;
    }
}
