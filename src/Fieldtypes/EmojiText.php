<?php

namespace Convoy\Emojis\Fieldtypes;

use Convoy\Emojis\Fieldtypes\Concerns\HasEmojiPickerConfig;
use Statamic\Fieldtypes\Text;

/**
 * A text field with an emoji picker attached.
 *
 * Inherits all config, processing and augmentation from the core Text fieldtype.
 */
class EmojiText extends Text
{
    use HasEmojiPickerConfig;

    protected static $handle = 'emoji_text';

    protected static $title = 'statamic-emojis::messages.fieldtypes.emoji_text';

    // Text is selectable in forms, but there's no front-end form view for this fieldtype.
    protected $selectableInForms = false;

    /** Icon in the fieldtype selector (raw SVG): Lucide "smile-plus", like the picker trigger. */
    public function icon()
    {
        return file_get_contents(__DIR__.'/../../resources/svg/smile-plus.svg');
    }

    protected function configFieldItems(): array
    {
        return [
            ...parent::configFieldItems(),
            $this->emojiPickerConfigSection(),
        ];
    }
}
