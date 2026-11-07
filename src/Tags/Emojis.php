<?php

namespace StatamicEmojis\Tags;

use Statamic\Fields\Value;
use Statamic\Tags\Tags;
use StatamicEmojis\CustomEmojis;
use StatamicEmojis\EmojiSets;
use StatamicEmojis\Values\EmojiValue;

// Named "emojis" (not "emoji") so it doesn't clash with fields named "emoji" ({{ emoji:url }}).
class Emojis extends Tags
{
    protected static $handle = 'emojis';

    public function __construct(protected EmojiSets $sets, protected CustomEmojis $custom)
    {
    }

    public function index(): string
    {
        return $this->sets->html($this->params->get('value'), $this->params->get('set'));
    }

    public function url(): ?string
    {
        return $this->info()['url'] ?? null;
    }

    public function label(): ?string
    {
        return $this->info()['label'] ?? null;
    }

    public function custom()
    {
        $emojis = $this->custom->all()->values()->map(fn (array $emoji) => [
            'shortcode' => $emoji['shortcode'],
            'emoji' => ":{$emoji['shortcode']}:",
            'label' => $emoji['label'],
            'url' => url($emoji['url']),
            'keywords' => $emoji['keywords'] ?? [],
        ])->all();

        return $emojis ?: $this->parseNoResults();
    }

    protected function info(): array
    {
        $value = $this->params->get('value');
        $value = trim((string) ($value instanceof Value ? $value->value() : $value));

        if ($value === '') {
            return [];
        }

        return (new EmojiValue($value, $this->sets->resolve($this->params->get('set'))))->toArray();
    }
}
