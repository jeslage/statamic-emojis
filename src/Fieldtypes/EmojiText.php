<?php

namespace StatamicEmojis\Fieldtypes;

use StatamicEmojis\Fieldtypes\Concerns\HasEmojiPickerConfig;
use Statamic\Facades\GraphQL;
use Statamic\Fieldtypes\Text;
use StatamicEmojis\EmojiSets;
use StatamicEmojis\GraphQL\EmojiTextValueType;
use StatamicEmojis\Values\EmojiTextValue;

class EmojiText extends Text
{
    use HasEmojiPickerConfig;

    protected static $handle = 'emoji_text';

    protected static $title = 'statamic-emojis::messages.fieldtypes.emoji_text';

    protected $selectableInForms = false;

    public function icon()
    {
        return file_get_contents(__DIR__.'/../../resources/svg/smile-plus.svg');
    }

    protected const UNSUPPORTED_CONFIG = ['input_type', 'autocomplete'];

    protected function configFieldItems(): array
    {
        $sections = array_map(function (array $section) {
            $section['fields'] = array_diff_key($section['fields'], array_flip(self::UNSUPPORTED_CONFIG));

            if (isset($section['fields']['character_limit'])) {
                $section['fields']['character_limit']['width'] = '100';
            }

            return $section;
        }, parent::configFieldItems());

        return [
            ...array_values(array_filter($sections, fn (array $section) => $section['fields'] !== [])),
            $this->emojiPickerConfigSection(),
        ];
    }

    public function augment($value)
    {
        $value = parent::augment($value);

        if (! is_string($value) || $value === '') {
            return $value;
        }

        return new EmojiTextValue($value, app(EmojiSets::class)->default());
    }

    public function toGqlType()
    {
        return GraphQL::type(EmojiTextValueType::NAME);
    }

    public function addGqlTypes()
    {
        GraphQL::addType(EmojiTextValueType::class);
    }
}
