<?php

namespace Convoy\Emojis\Fieldtypes;

use Statamic\Fieldtypes\Text;

/**
 * A text field with an emoji picker attached.
 *
 * Inherits all config, processing and augmentation from the core Text fieldtype.
 */
class Emoji extends Text
{
    protected static $handle = 'emoji';

    protected function configFieldItems(): array
    {
        $items = parent::configFieldItems();

        // TODO: add emoji-specific config, e.g. picker position, allowed categories, skin tone.
        // $items[] = [
        //     'display' => __('Emoji Picker'),
        //     'fields' => [ ... ],
        // ];

        return $items;
    }
}
