<?php

namespace StatamicEmojis\Modifiers;

use StatamicEmojis\EmojiSets;
use Statamic\Fields\Value;
use Statamic\Modifiers\Modifier;

/**
 * Replaces emojis in a value (single emoji, text or Bard HTML) with images of an emoji set.
 *
 * Antlers: {{ emoji | emoji_images }}, {{ content | emoji_images('openmoji') }}
 * Blade:   {!! Statamic::modify($page->augmentedValue('emoji'))->emoji_images() !!}
 *
 * Set: modifier parameter, else the site-wide set.
 */
class EmojiImages extends Modifier
{
    protected static $handle = 'emoji_images';

    public function __construct(protected EmojiSets $sets)
    {
    }

    public function index($value, $params)
    {
        $set = $this->sets->resolve($params[0] ?? null);

        if ($value instanceof Value) {
            $value = $value->value();
        }

        if (! is_string($value) && ! (is_object($value) && method_exists($value, '__toString'))) {
            return $value;
        }

        return $this->sets->replace((string) $value, $set);
    }
}
