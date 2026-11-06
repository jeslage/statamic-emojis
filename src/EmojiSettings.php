<?php

namespace Convoy\Emojis;

use Statamic\Addons\Addon as AddonInstance;
use Statamic\Facades\Addon;

/**
 * Site-wide settings from the Emojis CP page (Statamic addon settings, resources/addons/statamic-emojis.yaml),
 * falling back to config/statamic-emojis.php. Fields can override each of them.
 */
class EmojiSettings
{
    public const PACKAGE = 'convoy/statamic-emojis';

    public function addon(): ?AddonInstance
    {
        return Addon::get(self::PACKAGE);
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

    public function customEmojis(): bool
    {
        return $this->bool($this->get('custom_emojis'), (bool) config('statamic-emojis.custom.enabled', true));
    }

    public function recentEmojis(): bool
    {
        return $this->bool($this->get('recent_emojis'), true);
    }

    /** Default allowed categories, empty = all. */
    public function categories(): array
    {
        return array_values(array_intersect((array) $this->get('categories', []), array_keys(Emojibase::CATEGORIES)));
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
