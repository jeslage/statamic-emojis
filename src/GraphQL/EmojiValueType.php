<?php

namespace StatamicEmojis\GraphQL;

use Rebing\GraphQL\Support\Type;
use Statamic\Facades\GraphQL;

class EmojiValueType extends Type
{
    public const NAME = 'EmojiValue';

    protected $attributes = [
        'name' => self::NAME,
        'description' => 'An emoji: the Unicode emoji or a custom ":shortcode:", with its name and image',
    ];

    public function fields(): array
    {
        return [
            'value' => ['type' => GraphQL::string(), 'description' => 'The emoji, or ":shortcode:" for custom emojis'],
            'label' => ['type' => GraphQL::string(), 'description' => 'Name of the emoji'],
            'url' => ['type' => GraphQL::string(), 'description' => 'Image of the emoji set or custom emoji; null for the native set'],
            'set' => ['type' => GraphQL::string(), 'description' => 'Emoji set: native, noto, fluent, openmoji or a custom set'],
            'custom' => ['type' => GraphQL::boolean(), 'description' => 'Whether this is an uploaded custom emoji'],
            'shortcode' => ['type' => GraphQL::string(), 'description' => 'Shortcode of a custom emoji, without colons'],
        ];
    }
}
