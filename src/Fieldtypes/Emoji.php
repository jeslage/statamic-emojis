<?php

namespace Convoy\Emojis\Fieldtypes;

use Convoy\Emojis\Emojibase;
use Statamic\Fieldtypes\Text;

/**
 * A text field with an emoji picker attached.
 *
 * Inherits all config, processing and augmentation from the core Text fieldtype.
 */
class Emoji extends Text
{
    protected static $handle = 'emoji';

    /** Skin tones supported by Emojibase / vue-frimousse. */
    public const SKIN_TONES = ['none', 'light', 'medium-light', 'medium', 'medium-dark', 'dark'];

    /**
     * Icon in the fieldtype selector. Statamic accepts raw SVG markup;
     * same Lucide icon as the picker triggers in the Control Panel.
     */
    public function icon()
    {
        return file_get_contents(__DIR__.'/../../resources/svg/smile-plus.svg');
    }

    protected function configFieldItems(): array
    {
        $items = parent::configFieldItems();

        $items[] = [
            'display' => __('statamic-emojis::messages.config.section'),
            'fields' => [
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
                    'options' => collect(self::SKIN_TONES)
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

        return $items;
    }
}
