<?php

namespace StatamicEmojis\Modifiers;

use StatamicEmojis\EmojiSets;
use Statamic\Fields\Value;
use Statamic\Modifiers\Modifier;

class Emojis extends Modifier
{
    protected static $handle = 'emojis';

    public function __construct(protected EmojiSets $sets)
    {
    }

    public function index($value, $params)
    {
        $raw = $value instanceof Value ? $value->value() : $value;

        if (! is_string($raw) && ! (is_object($raw) && method_exists($raw, '__toString'))) {
            return $value;
        }

        return $this->sets->html($raw, $params[0] ?? null);
    }
}
