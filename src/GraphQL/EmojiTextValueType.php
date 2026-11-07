<?php

namespace StatamicEmojis\GraphQL;

use Rebing\GraphQL\Support\Type;
use Statamic\Facades\GraphQL;

class EmojiTextValueType extends Type
{
    public const NAME = 'EmojiTextValue';

    protected $attributes = [
        'name' => self::NAME,
        'description' => 'Text with emojis',
    ];

    public function fields(): array
    {
        return [
            'value' => ['type' => GraphQL::string(), 'description' => 'The text'],
            'html' => ['type' => GraphQL::string(), 'description' => 'The text as HTML, emojis as images of the field\'s set'],
            'set' => ['type' => GraphQL::string(), 'description' => 'Emoji set of the field'],
        ];
    }
}
