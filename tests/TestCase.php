<?php

namespace StatamicEmojis\Tests;

use StatamicEmojis\ServiceProvider;
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
    }

    protected function tearDown(): void
    {
        // AddonTestCase stores users in tests/__fixtures__; don't let them pile up between tests
        User::all()->each->delete();

        parent::tearDown();
    }
}
