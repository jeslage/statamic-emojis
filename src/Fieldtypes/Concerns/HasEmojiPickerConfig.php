<?php

namespace Convoy\Emojis\Fieldtypes\Concerns;

use Convoy\Emojis\Emojibase;
use Convoy\Emojis\EmojiSets;

/**
 * The "Emoji Picker" config section shared by the Emoji and Emoji Text fieldtypes.
 */
trait HasEmojiPickerConfig
{
    protected function emojiPickerConfigSection(): array
    {
        return [
            'display' => __('statamic-emojis::messages.config.section'),
            'fields' => [
                'emoji_set' => [
                    'display' => __('statamic-emojis::messages.config.emoji_set'),
                    'instructions' => __('statamic-emojis::messages.config.emoji_set_instructions'),
                    'type' => 'select',
                    'clearable' => true,
                    'placeholder' => __('statamic-emojis::messages.sets.default', [
                        'set' => __('statamic-emojis::messages.sets.'.app(EmojiSets::class)->default()),
                    ]),
                    'options' => collect(app(EmojiSets::class)->sets())
                        ->mapWithKeys(fn ($set) => [$set => __("statamic-emojis::messages.sets.{$set}")])
                        ->all(),
                ],
                'categories' => [
                    'display' => __('statamic-emojis::messages.config.categories'),
                    'instructions' => __('statamic-emojis::messages.config.categories_instructions'),
                    'type' => 'checkboxes',
                    'inline' => true,
                    'options' => collect(Emojibase::CATEGORIES)
                        ->keys()
                        ->mapWithKeys(fn ($key) => [$key => __("statamic-emojis::messages.categories.{$key}")])
                        ->all(),
                ],
                'skin_tone' => [
                    'display' => __('statamic-emojis::messages.config.skin_tone'),
                    'instructions' => __('statamic-emojis::messages.config.skin_tone_instructions'),
                    'type' => 'select',
                    'default' => 'none',
                    'options' => collect(Emojibase::SKIN_TONES)
                        ->mapWithKeys(fn ($tone) => [$tone => __("statamic-emojis::messages.skin_tones.{$tone}")])
                        ->all(),
                    'width' => 50,
                ],
                'skin_tone_selector' => [
                    'display' => __('statamic-emojis::messages.config.skin_tone_selector'),
                    'instructions' => __('statamic-emojis::messages.config.skin_tone_selector_instructions'),
                    'type' => 'toggle',
                    'default' => true,
                    'width' => 50,
                ],
            ],
        ];
    }
}
