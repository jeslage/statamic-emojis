<?php

namespace StatamicEmojis;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Emojibase
{
    /** Locales with Emojibase data. Keep in sync with resources/js/locale.js. */
    public const LOCALES = [
        'bn', 'da', 'de', 'en-gb', 'en', 'es-mx', 'es', 'et', 'fi', 'fr', 'hi', 'hu', 'it', 'ja',
        'ko', 'lt', 'ms', 'nb', 'nl', 'pl', 'pt', 'ru', 'sv', 'th', 'uk', 'vi', 'zh-hant', 'zh',
    ];

    /** Skin tones supported by Emojibase / vue-frimousse. */
    public const SKIN_TONES = ['none', 'light', 'medium-light', 'medium', 'medium-dark', 'dark'];

    /**
     * Picker categories (key => Emojibase group indexes in data.json).
     * Like Slack, "smileys & emotion" and "people & body" are merged into one category.
     * Group 2 ("component", skin tone and hair modifiers) is never shown.
     */
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

    /**
     * Emoji data, limited to the given category keys (empty = all categories).
     */
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

    /**
     * Translated group, subgroup and skin tone names.
     */
    public function messages(string $locale): array
    {
        return $this->fetch($locale, 'messages.json');
    }

    public static function isLocale(string $locale): bool
    {
        return in_array($locale, self::LOCALES, true);
    }

    /** Forget the cached data of all locales, so it's fetched again on next use. */
    public function clear(): void
    {
        foreach (self::LOCALES as $locale) {
            foreach (['data.json', 'messages.json'] as $file) {
                Cache::forget($this->cacheKey($locale, $file));
            }
        }
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

        return Cache::remember(
            $this->cacheKey($locale, $file),
            config('statamic-emojis.emojibase.cache_ttl'),
            fn () => Http::timeout(15)->get($url)->throw()->json()
        );
    }
}
