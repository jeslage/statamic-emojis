<?php

namespace StatamicEmojis;

use StatamicEmojis\Bard\CustomEmojiNode;
use StatamicEmojis\Console\Commands\ClearEmojiSet;
use StatamicEmojis\Console\Commands\SetupCustomEmojis;
use StatamicEmojis\Console\Commands\WarmEmojiSet;
use StatamicEmojis\Fieldtypes\CustomEmojiGrid;
use StatamicEmojis\Fieldtypes\Emoji;
use StatamicEmojis\Fieldtypes\EmojiSet;
use StatamicEmojis\Fieldtypes\EmojiText;
use StatamicEmojis\Fieldtypes\ImageCache;
use StatamicEmojis\Fieldtypes\SkinTone;
use Illuminate\Support\Facades\Event;
use Statamic\Events\AssetDeleted;
use Statamic\Events\AssetReuploaded;
use Statamic\Events\AssetSaved;
use Statamic\Events\AssetUploaded;
use Statamic\Facades\CP\Nav;
use Statamic\Fieldtypes\Bard\Augmentor;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $fieldtypes = [
        Emoji::class,
        EmojiText::class,
        EmojiSet::class,
        SkinTone::class,
        CustomEmojiGrid::class,
        ImageCache::class,
    ];

    protected $commands = [
        ClearEmojiSet::class,
        SetupCustomEmojis::class,
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
        $this->app->singleton(CustomEmojis::class);
        $this->app->singleton(EmojiSettings::class);

        $this->registerCustomEmojiDisk();
    }

    public function bootAddon()
    {
        $custom = app(CustomEmojis::class);
        $settings = app(EmojiSettings::class);

        $this->registerSettingsBlueprint(fn () => SettingsBlueprint::contents());

        Statamic::provideToScript([
            'statamicEmojis' => [
                'set' => app(EmojiSets::class)->default(),
                'imageUrl' => app(EmojiSets::class)->baseUrl(),
                'custom' => $custom->isEnabled(),
                ...$settings->toScript(),
            ],
        ]);

        Augmentor::addExtension('customEmoji', new CustomEmojiNode);

        Event::listen(
            [AssetSaved::class, AssetUploaded::class, AssetReuploaded::class, AssetDeleted::class],
            function ($event) use ($custom) {
                if ($custom->owns($event->asset)) {
                    $custom->clearCache();
                }
            },
        );

        Nav::extend(function ($nav) use ($settings) {
            $nav->settings(__('statamic-emojis::messages.custom.nav'))
                ->route('statamic-emojis.index')
                ->icon(file_get_contents(__DIR__.'/../resources/svg/smile.svg'))
                ->can('editSettings', $settings->addon());
        });
    }

    protected function registerCustomEmojiDisk(): void
    {
        $disk = config('statamic-emojis.custom.disk', 'statamic-emojis');

        if (config("filesystems.disks.{$disk}")) {
            return;
        }

        config(["filesystems.disks.{$disk}" => [
            'driver' => 'local',
            'root' => public_path('statamic-emojis'),
            'url' => '/statamic-emojis',
            'visibility' => 'public',
            'throw' => false,
        ]]);
    }
}
