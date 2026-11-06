<?php

namespace StatamicEmojis\Values;

use Statamic\Fields\ArrayableString;
use StatamicEmojis\EmojiSets;

/**
 * Augmented value of the Emoji Text fieldtype: the text in templates, and in the API
 * {value, html, set} where html has the emojis (and custom ":shortcodes:") as images of the field's set.
 */
class EmojiTextValue extends ArrayableString
{
    protected ?array $resolved = null;

    public function __construct(string $value, string $set)
    {
        parent::__construct($value, ['set' => $set]);
    }

    public function set(): string
    {
        return $this->extra()['set'];
    }

    public function toArray()
    {
        return $this->resolved ??= [
            'value' => (string) $this->value,
            'html' => app(EmojiSets::class)->replace(e((string) $this->value), $this->set()),
            'set' => $this->set(),
        ];
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
