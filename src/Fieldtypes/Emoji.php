<?php

namespace StatamicEmojis\Fieldtypes;

use StatamicEmojis\Fieldtypes\Concerns\HasEmojiPickerConfig;
use Statamic\Fields\Fieldtype;

/**
 * A single emoji, chosen with the emoji picker.
 */
class Emoji extends Fieldtype
{
    use HasEmojiPickerConfig;

    protected static $handle = 'emoji';

    protected static $title = 'statamic-emojis::messages.fieldtypes.emoji';

    protected $categories = ['controls'];

    /** Icon in the fieldtype selector (raw SVG): Lucide "smile". */
    public function icon()
    {
        return file_get_contents(__DIR__.'/../../resources/svg/smile.svg');
    }

    protected function configFieldItems(): array
    {
        return [$this->emojiPickerConfigSection()];
    }

    public function process($data)
    {
        $data = is_string($data) ? trim($data) : null;

        return $data === '' ? null : $data;
    }

    public function augment($value)
    {
        return $value;
    }
}
