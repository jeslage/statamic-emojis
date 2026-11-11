<?php

namespace StatamicEmojis\Tests;

use StatamicEmojis\ServiceProvider;
use Illuminate\Support\Facades\Http;
use Statamic\Addons\Manifest;
use Statamic\Facades\User;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        // Tests create several CP users; Statamic Pro is free for local development
        $app['config']->set('statamic.editions.pro', true);

        // AddonTestCase's manifest leaves out the editions from composer.json ("free", then "pro")
        $manifest = $app->make(Manifest::class);
        $package = array_key_first($manifest->manifest);
        $json = json_decode(file_get_contents(__DIR__.'/../composer.json'), true);
        $manifest->manifest[$package]['editions'] = $json['extra']['statamic']['editions'] ?? [];

        $app['config']->set("statamic.editions.addons.{$package}", 'pro');
    }

    // Http::fake stub for the Emojibase data the image route checks codes against; put it before catch-alls.
    protected function emojibaseStub(?array $emojis = null): array
    {
        return ['*/en/data.json' => Http::response($emojis ?? [
            ['emoji' => '😀', 'label' => 'grinning face'],
            ['emoji' => '❤️', 'label' => 'red heart'],
            ['emoji' => '1️⃣', 'label' => 'keycap: 1'],
            ['emoji' => '👍', 'label' => 'thumbs up', 'skins' => [['emoji' => '👍🏽', 'label' => 'thumbs up: medium skin tone']]],
        ])];
    }

    // Requests sent to a set's CDN, leaving out the Emojibase data.
    protected function cdnRequests(): int
    {
        return collect(Http::recorded())->reject(fn ($pair) => str_ends_with($pair[0]->url(), '/data.json'))->count();
    }

    protected function tearDown(): void
    {
        // AddonTestCase stores users in tests/__fixtures__; don't let them pile up between tests
        User::all()->each->delete();

        parent::tearDown();
    }
}
