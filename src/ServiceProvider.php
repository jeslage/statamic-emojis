<?php

namespace Convoy\Emojis;

use Convoy\Emojis\Console\Commands\ClearEmojiSet;
use Convoy\Emojis\Console\Commands\WarmEmojiSet;
use Convoy\Emojis\Fieldtypes\Emoji;
use Convoy\Emojis\Fieldtypes\EmojiText;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $fieldtypes = [
        Emoji::class,
        EmojiText::class,
    ];

    protected $commands = [
        ClearEmojiSet::class,
        WarmEmojiSet::class,
    ];

    protected $vite = [
        'input' => [
            'resources/js/addon.js',
            'resources/css/addon.css',
        ],
        'publicDirectory' => 'resources/dist',
    ];

    public function register()
    {
        parent::register();

        $this->app->singleton(EmojiSets::class);
    }

    public function bootAddon()
    {
        // Site-wide emoji set and the image route for the CP picker (Statamic.$config.get('statamicEmojis'))
        Statamic::provideToScript([
            'statamicEmojis' => [
                'set' => app(EmojiSets::class)->default(),
                'imageUrl' => app(EmojiSets::class)->baseUrl(),
            ],
        ]);
    }
}
