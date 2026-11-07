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
    /** Emoji names per locale, including skin tone variants (emoji => label). */
    protected array $labels = [];

    /**
     * Emojibase locale for a Laravel/Statamic locale (e.g. "de_CH" -> "de", "zh_TW" -> "zh-hant").
     * Same as emojibaseLocale() in resources/js/locale.js.
     */
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

    /** Names of all emojis and their skin tone variants in a locale, e.g. ['👍🏻' => 'thumbs up: light skin tone']. */
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

    /** Shortcode preset used for suggestions (English, like Slack/GitHub): emojibase. */
    public const SHORTCODES = 'shortcodes/emojibase.json';

    /**
     * Shortcodes per hexcode, e.g. ['1F680' => 'rocket', '1F44D' => ['+1', 'thumbsup', 'yes']].
     * English for every locale, as shortcodes are typed rather than read.
     */
    public function shortcodes(): array
    {
        return $this->fetch('en', self::SHORTCODES);
    }

    /** Forget the cached data of all locales, so it's fetched again on next use. */
    public function clear(): void
    {
        foreach (self::LOCALES as $locale) {
            foreach (['data.json', 'messages.json'] as $file) {
                Cache::forget($this->cacheKey($locale, $file));
            }
        }

        Cache::forget($this->cacheKey('en', self::SHORTCODES));
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
