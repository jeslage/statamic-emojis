<?php

namespace StatamicEmojis;

use Statamic\Addons\Addon as AddonInstance;
use Statamic\Facades\Addon;

/**
 * Site-wide settings from the Emojis CP page (Statamic addon settings, resources/addons/statamic-emojis.yaml),
 * falling back to config/statamic-emojis.php. Fields can override each of them except the emoji set.
 */
class EmojiSettings
{
    public const PACKAGE = 'jeslage/statamic-emojis';

    /** Where to buy Pro (Statamic Marketplace). */
    public const PRO_URL = 'https://statamic.com/addons/jeslage/emojis';

    public function addon(): ?AddonInstance
    {
        return Addon::get(self::PACKAGE);
    }

    /**
     * Pro edition (config/statamic/editions.php: 'addons' => ['jeslage/statamic-emojis' => 'pro']).
     * Pro adds image sets (Noto, Fluent, OpenMoji and custom sets) and custom emojis.
     */
    public function pro(): bool
    {
        return $this->addon()?->edition() === 'pro';
    }

    public function get(string $key, $default = null)
    {
        // Only values saved on the Emojis page; unsaved ones fall back to the config (not the blueprint defaults)
        $value = data_get($this->addon()?->settings()->raw() ?? [], $key);

        return $value === null || $value === '' || $value === [] ? $default : $value;
    }

    /** Site-wide emoji set (before validation against the configured sets). */
    public function set(): string
    {
        return (string) $this->get('emoji_set', config('statamic-emojis.set', EmojiSets::NATIVE));
    }

    /** Custom emojis are part of Pro. */
    public function customEmojis(): bool
    {
        return $this->pro() && $this->bool($this->get('custom_emojis'), (bool) config('statamic-emojis.custom.enabled', true));
    }

    public function recentEmojis(): bool
    {
        return $this->bool($this->get('recent_emojis'), true);
    }

    /** Shortcode suggestions while typing ":name" in Emoji Text and Bard. */
    public function suggestions(): bool
    {
        return $this->bool($this->get('suggestions'), true);
    }

    /** Default allowed categories; empty = all (also when all are selected, so the picker has no category limit). */
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

    /** Picker defaults for the Control Panel (fields fall back to these). */
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

    /**
     * A field's on/off override: "show"/"hide" (or legacy true/false); anything else uses the default.
     */
    public static function bool($value, bool $default): bool
    {
        return match (true) {
            $value === true, $value === 'show', $value === 'true', $value === 1, $value === '1' => true,
            $value === false, $value === 'hide', $value === 'false', $value === 0, $value === '0' => false,
            default => $default,
        };
    }
}
