<?php

namespace Convoy\Emojis\Fieldtypes\Concerns;

use Convoy\Emojis\EmojiFields;

/**
 * The "Emoji Picker" config section shared by the Emoji and Emoji Text fieldtypes.
 * Empty values use the site-wide settings from the Emojis CP page.
 */
trait HasEmojiPickerConfig
{
    protected function emojiPickerConfigSection(): array
    {
        return [
            'display' => __('statamic-emojis::messages.config.section'),
            'fields' => [
                'emoji_set' => EmojiFields::emojiSet(inherit: true),
                'custom_emojis' => EmojiFields::customEmojis(inherit: true),
                'categories' => EmojiFields::categories(inherit: true),
                'skin_tone' => EmojiFields::skinTone(inherit: true),
                'skin_tone_selector' => EmojiFields::skinToneSelector(inherit: true),
            ],
        ];
    }
}
