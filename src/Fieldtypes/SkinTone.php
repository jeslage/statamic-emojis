<?php

namespace StatamicEmojis\Fieldtypes;

use StatamicEmojis\Emojibase;
use Statamic\Fields\Fieldtype;

class SkinTone extends Fieldtype
{
    protected static $handle = 'statamic_emojis_skin_tone';

    protected $selectable = false;

    public function process($data)
    {
        return in_array($data, Emojibase::SKIN_TONES, true) ? $data : null;
    }
}
