<?php

namespace StatamicEmojis\Fieldtypes;

use StatamicEmojis\EmojiSets;
use Statamic\Fields\Fieldtype;

class ImageCache extends Fieldtype
{
    protected static $handle = 'statamic_emojis_image_cache';

    protected $selectable = false;

    public function preload()
    {
        return [
            'status' => app(EmojiSets::class)->cacheStatus(),
            'urls' => [
                'status' => cp_route('statamic-emojis.cache.status'),
                'warm' => cp_route('statamic-emojis.cache.warm', ['set' => '__SET__']),
                'clear' => cp_route('statamic-emojis.cache.clear', ['set' => '__SET__']),
            ],
        ];
    }

    public function process($data)
    {
        return null;
    }
}
