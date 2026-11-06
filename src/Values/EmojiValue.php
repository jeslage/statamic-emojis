<?php

namespace StatamicEmojis\Values;

use Statamic\Facades\Site;
use Statamic\Fields\ArrayableString;
use StatamicEmojis\CustomEmojis;
use StatamicEmojis\Emojibase;
use StatamicEmojis\EmojiSets;

/**
 * Augmented value of the Emoji fieldtype, like Statamic's Link value:
 * a string in templates ({{ emoji }}, {{ $page->emoji }}), an object in the REST and GraphQL API,
 * and {{ emoji:label }}, {{ emoji:url }} etc. in Antlers.
 *
 * Value: the stored emoji ("🎉") or custom emoji (":party-parrot:"). The extras are resolved lazily,
 * so plain template output costs nothing.
 */
class EmojiValue extends ArrayableString
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

    public function isCustom(): bool
    {
        return (bool) preg_match(CustomEmojis::SHORTCODE_PATTERN, (string) $this->value)
            && str_starts_with((string) $this->value, ':');
    }

    /** {value, label, url, set, custom, shortcode} */
    public function toArray()
    {
        return $this->resolved ??= $this->resolve();
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    protected function resolve(): array
    {
        $emoji = (string) $this->value;
        $set = $this->set();

        if ($this->isCustom()) {
            $custom = app(CustomEmojis::class)->find($emoji);

            return [
                'value' => $emoji,
                'label' => $custom['label'] ?? null,
                'url' => isset($custom['url']) ? url($custom['url']) : null,
                'set' => $set,
                'custom' => true,
                'shortcode' => trim($emoji, ':'),
            ];
        }

        $sets = app(EmojiSets::class);

        return [
            'value' => $emoji,
            'label' => $this->label($emoji),
            'url' => $sets->isImageSet($set) ? $sets->url($set, $emoji) : null,
            'set' => $set,
            'custom' => false,
            'shortcode' => null,
        ];
    }

    protected function label(string $emoji): ?string
    {
        try {
            $labels = app(Emojibase::class)->labels(Site::current()?->lang() ?? 'en');
        } catch (\Throwable) {
            return null; // Emojibase data unreachable
        }

        // Stored emojis may lack the variation selector FE0F the data has (or the other way round)
        return $labels[$emoji]
            ?? $labels[$emoji."\u{FE0F}"]
            ?? $labels[str_replace("\u{FE0F}", '', $emoji)]
            ?? null;
    }
}
