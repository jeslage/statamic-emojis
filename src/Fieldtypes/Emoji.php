<?php

namespace StatamicEmojis\Fieldtypes;

use StatamicEmojis\Fieldtypes\Concerns\HasEmojiPickerConfig;
use Statamic\Facades\GraphQL;
use Statamic\Fields\Fieldtype;
use StatamicEmojis\EmojiSets;
use StatamicEmojis\GraphQL\EmojiValueType;
use StatamicEmojis\Values\EmojiValue;

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

    /** A string in templates, an object with name, image and set in the API (see EmojiValue). */
    public function augment($value)
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        return new EmojiValue($value, app(EmojiSets::class)->default());
    }

    public function toGqlType()
    {
        return GraphQL::type(EmojiValueType::NAME);
    }

    public function addGqlTypes()
    {
        GraphQL::addType(EmojiValueType::class);
    }
}
