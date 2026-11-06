<?php

namespace StatamicEmojis\Modifiers;

use StatamicEmojis\EmojiSets;
use StatamicEmojis\Values\EmojiTextValue;
use StatamicEmojis\Values\EmojiValue;
use Statamic\Fields\Value;
use Statamic\Modifiers\Modifier;

/**
 * Replaces emojis in a value (single emoji, text or Bard HTML) with images of an emoji set.
 *
 * Antlers: {{ emoji | emoji_images }}, {{ content | emoji_images('openmoji') }}
 * Blade:   {!! Statamic::modify($page->augmentedValue('emoji'))->emoji_images() !!}
 *
 * Set: modifier parameter, else the field's "emoji_set" setting, else the site-wide set.
 */
class EmojiImages extends Modifier
{
    protected static $handle = 'emoji_images';

    public function __construct(protected EmojiSets $sets)
    {
    }

    public function index($value, $params)
    {
        $set = $this->sets->resolve($params[0] ?? $this->fieldSet($value));

        if ($value instanceof Value) {
            $value = $value->value();
        }

        if (! is_string($value) && ! (is_object($value) && method_exists($value, '__toString'))) {
            return $value;
        }

        return $this->sets->replace((string) $value, $set);
    }

    /**
     * The "emoji_set" of the field the value belongs to: values of the Emoji and Emoji Text fieldtypes
     * carry their set (in Antlers and Blade alike). Other values use the site-wide set.
     */
    protected function fieldSet($value): ?string
    {
        if ($value instanceof Value) {
            $value = $value->value();
        }

        return $value instanceof EmojiValue || $value instanceof EmojiTextValue ? $value->set() : null;
    }
}
