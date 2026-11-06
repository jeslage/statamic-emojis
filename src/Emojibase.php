<?php

namespace Convoy\Emojis;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Emojibase
{
    /** Locales with Emojibase data. Keep in sync with resources/js/locale.js. */
    public const LOCALES = [
        'bn', 'da', 'de', 'en-gb', 'en', 'es-mx', 'es', 'et', 'fi', 'fr', 'hi', 'hu', 'it', 'ja',
        'ko', 'lt', 'ms', 'nb', 'nl', 'pl', 'pt', 'ru', 'sv', 'th', 'uk', 'vi', 'zh-hant', 'zh',
    ];

    /**
     * Emojibase groups (key => group index in data.json) that the picker shows.
     * Group 2 ("component", skin tone and hair modifiers) is never shown as a category.
     */
    public const CATEGORIES = [
        'smileys-emotion' => 0,
        'people-body' => 1,
        'animals-nature' => 3,
        'food-drink' => 4,
        'travel-places' => 5,
        'activities' => 6,
        'objects' => 7,
        'symbols' => 8,
        'flags' => 9,
    ];

    /**
     * Emoji data, limited to the given category keys (empty = all categories).
     */
    public function data(string $locale, array $categories = []): array
    {
        $emojis = $this->fetch($locale, 'data.json');

        $groups = collect($categories)
            ->map(fn ($key) => self::CATEGORIES[$key] ?? null)
            ->filter(fn ($group) => $group !== null)
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

    protected function fetch(string $locale, string $file): array
    {
        $url = rtrim(config('statamic-emojis.emojibase.url'), '/')."/{$locale}/{$file}";

        return Cache::remember(
            'statamic-emojis:emojibase:'.md5($url),
            config('statamic-emojis.emojibase.cache_ttl'),
            fn () => Http::timeout(15)->get($url)->throw()->json()
        );
    }
}
