<?php

namespace StatamicEmojis\Fieldtypes;

use StatamicEmojis\EmojiSets;
use Statamic\Fields\Fieldtype;

/**
 * Emoji set setting of the Emojis page: a select showing an example emoji in each set.
 */
class EmojiSet extends Fieldtype
{
    protected static $handle = 'statamic_emojis_set';

    protected $selectable = false;

    public function process($data)
    {
        return in_array($data, app(EmojiSets::class)->sets(), true) ? $data : null;
    }
}
