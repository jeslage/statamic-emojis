<?php

namespace StatamicEmojis;

use Statamic\Addons\Addon as AddonInstance;
use Statamic\Facades\Addon;

class EmojiSettings
{
    public const PACKAGE = 'jeslage/statamic-emojis';

    public const PRO_URL = 'https://statamic.com/addons/jeslage/emojis';

    public function addon(): ?AddonInstance
    {
        return Addon::get(self::PACKAGE);
    }

    public function pro(): bool
    {
        return $this->addon()?->edition() === 'pro';
    }

    public function get(string $key, $default = null)
    {
        // Only saved values; unsaved ones fall back to the config, not the blueprint defaults
        $value = data_get($this->addon()?->settings()->raw() ?? [], $key);

        return $value === null || $value === '' || $value === [] ? $default : $value;
    }

    public function set(): string
    {
        return (string) $this->get('emoji_set', config('statamic-emojis.set', EmojiSets::NATIVE));
    }

    public function customEmojis(): bool
    {
        return $this->pro() && $this->bool($this->get('custom_emojis'), (bool) config('statamic-emojis.custom.enabled', true));
    }

    public function recentEmojis(): bool
    {
        return $this->bool($this->get('recent_emojis'), true);
    }

    public function suggestions(): bool
    {
        return $this->bool($this->get('suggestions'), true);
    }

    // All selected = empty, so the picker has no category limit
    public function categories(): array
    {
        $all = array_keys(Emojibase::CATEGORIES);
        $categories = array_values(array_intersect($all, (array) $this->get('categories', [])));

        return count($categories) === count($all) ? [] : $categories;
    }

    public function skinTone(): string
    {
        $tone = $this->get('skin_tone', 'none');

        return in_array($tone, Emojibase::SKIN_TONES, true) ? $tone : 'none';
    }

    public function skinToneSelector(): bool
    {
        return $this->bool($this->get('skin_tone_selector'), true);
    }

    public function toScript(): array
    {
        return [
            'categories' => $this->categories(),
            'skinTone' => $this->skinTone(),
            'skinToneSelector' => $this->skinToneSelector(),
            'recent' => $this->recentEmojis(),
            'suggestions' => $this->suggestions(),
        ];
    }

    public static function bool($value, bool $default): bool
    {
        return match (true) {
            $value === true, $value === 'show', $value === 'true', $value === 1, $value === '1' => true,
            $value === false, $value === 'hide', $value === 'false', $value === 0, $value === '0' => false,
            default => $default,
        };
    }
}
