<?php

namespace StatamicEmojis\Fieldtypes\Concerns;

use StatamicEmojis\EmojiFields;
use StatamicEmojis\EmojiSettings;

trait HasEmojiPickerConfig
{
    protected function emojiPickerConfigSection(): array
    {
        return [
            'display' => __('statamic-emojis::messages.config.section'),
            'fields' => [
                ...(app(EmojiSettings::class)->pro() ? ['custom_emojis' => EmojiFields::customEmojis(inherit: true)] : []),
                'categories' => EmojiFields::categories(inherit: true),
                'skin_tone' => EmojiFields::skinTone(inherit: true),
                'skin_tone_selector' => EmojiFields::skinToneSelector(inherit: true),
            ],
        ];
    }
}
