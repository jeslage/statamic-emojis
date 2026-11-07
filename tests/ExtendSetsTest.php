<?php

namespace StatamicEmojis\Tests;

use StatamicEmojis\Contracts\SetNaming;
use StatamicEmojis\EmojiSets;
use StatamicEmojis\EmojiFields;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class ExtendSetsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['statamic-emojis.images.cache_path' => $this->cachePath()]);
        File::deleteDirectory($this->cachePath());
    }

    protected function tearDown(): void
    {
        EmojiSets::flushExtended();
        File::deleteDirectory($this->cachePath());

        parent::tearDown();
    }

    private function cachePath(): string
    {
        return sys_get_temp_dir().'/statamic-emojis-extend-tests';
    }

    private function sets(): EmojiSets
    {
        return app(EmojiSets::class);
    }

    public function test_built_in_sets_are_there_without_config(): void
    {
        config(['statamic-emojis.sets' => []]);

        $this->assertSame(['native', 'noto', 'fluent', 'openmoji'], $this->sets()->sets());
    }

    public function test_config_sets_are_added_to_the_built_in_ones(): void
    {
        config(['statamic-emojis.sets' => [
            'twemoji' => ['label' => 'Twemoji', 'url' => 'https://cdn.example.com/twemoji/svg'],
        ]]);

        $this->assertSame(['native', 'noto', 'fluent', 'openmoji', 'twemoji'], $this->sets()->sets());
        $this->assertSame('Twemoji', $this->sets()->label('twemoji'));
        $this->assertContains('2764.svg', $this->sets()->candidates('twemoji', '2764-fe0f')); // default naming "fluent"
    }

    public function test_config_entries_override_single_keys_of_built_in_sets(): void
    {
        config(['statamic-emojis.sets' => ['noto' => ['url' => 'https://emojis.example.com/noto']]]);

        $noto = $this->sets()->definition('noto');
        $this->assertSame('https://emojis.example.com/noto', $noto['url']);
        $this->assertSame('noto', $noto['naming']);
        $this->assertSame('Noto', $this->sets()->label('noto'));
    }

    public function test_sets_can_be_added_in_code(): void
    {
        EmojiSets::extend('twemoji', ['label' => 'Twemoji', 'url' => 'https://cdn.example.com/twemoji/svg']);

        $this->assertTrue($this->sets()->isImageSet('twemoji'));
        $this->assertArrayHasKey('twemoji', EmojiFields::emojiSet()['options']);
    }

    public function test_sets_can_be_disabled(): void
    {
        config(['statamic-emojis.disabled_sets' => ['fluent']]);
        $this->assertSame(['native', 'noto', 'openmoji'], $this->sets()->sets());

        config(['statamic-emojis.disabled_sets' => ['*'], 'statamic-emojis.set' => 'noto']);
        $this->assertSame(['native'], $this->sets()->sets());
        $this->assertSame('native', $this->sets()->default());
        $this->get('/!/statamic-emojis/sets/noto/1f600.svg')->assertNotFound();
    }

    public function test_invalid_names_and_sets_without_url_are_ignored(): void
    {
        config(['statamic-emojis.sets' => [
            'my-set' => ['url' => 'https://cdn.example.com/a'],
            'native' => ['url' => 'https://cdn.example.com/b'],
            'nourl' => ['label' => 'No URL'],
        ]]);

        $this->assertSame(['native', 'noto', 'fluent', 'openmoji'], $this->sets()->sets());
    }

    public function test_custom_naming_classes(): void
    {
        config(['statamic-emojis.sets' => [
            'named' => ['url' => 'https://cdn.example.com/named', 'naming' => UnderscoreNaming::class],
        ]]);

        $this->assertSame(
            ['u_1f9d1_200d_1f680.svg'],
            $this->sets()->candidates('named', '1f9d1-200d-1f680'),
        );
    }

    public function test_added_sets_are_served_like_built_in_ones(): void
    {
        config(['statamic-emojis.sets' => ['twemoji' => ['url' => 'https://cdn.example.com/twemoji/svg']]]);
        Http::fake([
            'cdn.example.com/twemoji/svg/2764.svg' => Http::response('<svg id="heart"></svg>'),
            '*' => Http::response('', 404),
        ]);

        $this->get('/!/statamic-emojis/sets/twemoji/2764-fe0f.svg')
            ->assertOk()
            ->assertSee('<svg id="heart"></svg>', false);
    }
}

class UnderscoreNaming implements SetNaming
{
    public function files(array $codepoints): array
    {
        return in_array('fe0f', $codepoints, true) ? [] : ['u_'.implode('_', $codepoints).'.svg'];
    }
}
