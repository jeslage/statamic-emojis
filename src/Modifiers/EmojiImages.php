<?php

namespace Convoy\Emojis\Modifiers;

use Convoy\Emojis\EmojiSets;
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

    /** Fieldtypes that have the "emoji_set" setting. */
    protected const FIELDTYPES = ['emoji', 'emoji_text'];

    public function __construct(protected EmojiSets $sets)
    {
    }

    public function index($value, $params, $context = [])
    {
        $set = $this->sets->resolve($params[0] ?? $this->fieldSet($value, $context));

        if ($value instanceof Value) {
            $value = $value->value();
        }

        if (! is_string($value) && ! (is_object($value) && method_exists($value, '__toString'))) {
            return $value;
        }

        return $this->sets->replace((string) $value, $set);
    }

    /**
     * The "emoji_set" of the field the value belongs to.
     *
     * Blade passes the Value itself. Antlers unwraps it before calling modifiers, so look for the
     * emoji field in the current context that holds this value. If fields with different sets hold
     * the same value, it's ambiguous: use the site-wide set (or pass the set as a parameter).
     */
    protected function fieldSet($value, $context): ?string
    {
        if ($value instanceof Value) {
            return $value->field()?->get('emoji_set');
        }

        if (! is_string($value) || ! is_array($context)) {
            return null;
        }

        $sets = collect($context)
            ->filter(fn ($item) => $item instanceof Value
                && in_array($item->fieldtype()?->handle(), self::FIELDTYPES, true)
                && $item->raw() === $value)
            ->map(fn (Value $item) => $item->field()?->get('emoji_set'))
            ->unique()
            ->values();

        return $sets->count() === 1 ? $sets->first() : null;
    }
}
