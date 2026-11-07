<?php

namespace StatamicEmojis\Tests;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Blueprint;
use StatamicEmojis\CustomEmojis;
use StatamicEmojis\Values\EmojiValue;

class TagsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['statamic-emojis.set' => 'native']);
        Http::fake(['*/en/data.json' => Http::response([
            ['emoji' => '🎉', 'hexcode' => '1F389', 'label' => 'party popper', 'group' => 6],
        ])]);

        Storage::fake('statamic-emojis', ['url' => '/statamic-emojis']);
        config(['statamic-emojis.custom.enabled' => true, 'statamic-emojis.custom.container' => 'statamic-emojis']);
        $this->artisan('emojis:custom-setup')->assertSuccessful();
        Storage::disk('statamic-emojis')->put('party.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>');
        AssetContainer::find('statamic-emojis')->makeAsset('party.svg')->data(['title' => 'Party'])->save();
        app(CustomEmojis::class)->clearCache();

        View::addLocation(__DIR__.'/views');
    }

    protected function tearDown(): void
    {
        AssetContainer::find('statamic-emojis')?->delete();
        Blueprint::find('assets/statamic-emojis')?->delete();

        parent::tearDown();
    }

    public function test_antlers_tag(): void
    {
        [$text, $noto, $url, $label, $customUrl, $custom] = explode('|', trim(view('emojis-tag')->render()));

        // Native set: Unicode emojis stay characters, custom emojis become images
        $this->assertStringStartsWith('Hi 🎉 <img class="emoji emoji-custom" src="/statamic-emojis/party.svg"', $text);
        $this->assertStringContainsString('/sets/noto/1f389.svg', $noto);
        $this->assertStringEndsWith('/!/statamic-emojis/sets/noto/1f389.svg', $url);
        $this->assertSame('party popper', $label);
        $this->assertStringEndsWith('/statamic-emojis/party.svg', $customUrl);
        $this->assertSame('party=Party;', $custom);
    }

    public function test_tag_in_blade(): void
    {
        [$mood, $noto, $url, $custom] = explode('|', trim(view('emojis-tag-blade', [
            'mood' => new EmojiValue(':party:', 'native'),
        ])->render()));

        $this->assertStringContainsString('src="/statamic-emojis/party.svg"', $mood);
        $this->assertStringContainsString('/sets/noto/1f389.svg', $noto);
        $this->assertStringEndsWith('/sets/noto/1f389.svg', $url);
        $this->assertSame('party;', $custom);
    }
}
