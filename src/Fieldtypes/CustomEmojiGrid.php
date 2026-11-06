<?php

namespace StatamicEmojis\Fieldtypes;

use StatamicEmojis\CustomEmojis;
use Statamic\Fields\Fieldtype;

/**
 * Display-only field on the Emojis page: the uploaded custom emojis as a grid.
 */
class CustomEmojiGrid extends Fieldtype
{
    protected static $handle = 'statamic_emojis_custom_grid';

    protected $selectable = false;

    public function preload()
    {
        $custom = app(CustomEmojis::class);
        $container = $custom->container();

        return [
            'emojis' => $container ? $custom->all()->values()->all() : [],
            'container_url' => $container?->showUrl(),
            'setup_command' => 'php please emojis:custom-setup',
        ];
    }

    public function process($data)
    {
        return null;
    }
}
