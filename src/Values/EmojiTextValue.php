<?php

namespace StatamicEmojis\Values;

use Statamic\Fields\ArrayableString;
use StatamicEmojis\EmojiSets;

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
