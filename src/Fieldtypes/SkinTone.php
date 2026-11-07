<?php

namespace StatamicEmojis\Fieldtypes;

use StatamicEmojis\Emojibase;
use Statamic\Fields\Fieldtype;

/**
 * Skin tone setting of the emoji picker, as a toggle group of 👋 in each tone.
 * Used on the Emojis page and in the fields' "Emoji Picker" config (where empty = site-wide setting).
 */
class SkinTone extends Fieldtype
{
    protected static $handle = 'statamic_emojis_skin_tone';

    protected $selectable = false;

    public function process($data)
    {
        return in_array($data, Emojibase::SKIN_TONES, true) ? $data : null;
    }
}
