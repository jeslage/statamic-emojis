<?php

namespace Convoy\Emojis\Tests;

use Convoy\Emojis\ServiceProvider;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;
}
