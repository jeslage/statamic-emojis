<?php

namespace StatamicEmojis\Tests;

use StatamicEmojis\CustomEmojis;
use StatamicEmojis\EmojiSets;
use StatamicEmojis\EmojiSettings;
use StatamicEmojis\Fieldtypes\Emoji;
use StatamicEmojis\SettingsBlueprint;
use Statamic\Facades\AssetContainer;
use Statamic\Modifiers\Modify;

/**
 * The free edition: native emojis only, no image sets and no custom emojis (both part of Pro).
 */
class FreeEditionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['statamic.editions.addons.jeslage/statamic-emojis' => 'free']);
    }

    public function test_the_free_edition_is_the_default(): void
    {
        config(['statamic.editions.addons.jeslage/statamic-emojis' => null]);

        $this->assertSame('free', app(EmojiSettings::class)->addon()->edition());
        $this->assertFalse(app(EmojiSettings::class)->pro());
    }

    public function test_there_are_no_image_sets(): void
    {
        config(['statamic-emojis.set' => 'noto']);
        $sets = app(EmojiSets::class);

        $this->assertSame([EmojiSets::NATIVE], $sets->sets());
        $this->assertFalse($sets->isImageSet('noto'));
        $this->assertSame(EmojiSets::NATIVE, $sets->default());
        $this->assertSame(EmojiSets::NATIVE, $sets->resolve('openmoji'));
    }

    public function test_the_modifier_keeps_native_emojis(): void
    {
        $this->assertSame('Hi 🎉', (string) Modify::value('Hi 🎉')->emojis('noto'));
    }

    public function test_set_images_are_not_served(): void
    {
        $this->get('/!/statamic-emojis/sets/noto/1f389.svg')->assertNotFound();
    }

    public function test_custom_emojis_are_off(): void
    {
        config(['statamic-emojis.custom.enabled' => true]);

        $this->assertFalse(app(EmojiSettings::class)->customEmojis());
        $this->assertFalse(app(CustomEmojis::class)->isEnabled());
        $this->assertSame([], app(CustomEmojis::class)->all()->all());
    }

    public function test_fields_have_no_custom_emojis_option(): void
    {
        $handles = collect((new Emoji)->configFields()->all())->keys();

        $this->assertFalse($handles->contains('custom_emojis'));
        $this->assertTrue($handles->contains('skin_tone'));
    }

    public function test_the_settings_page_shows_pro_hints_instead_of_the_pro_tabs(): void
    {
        $tabs = SettingsBlueprint::contents()['tabs'];
        $handles = fn (string $tab) => collect($tabs[$tab]['sections'])->flatMap(fn ($s) => collect($s['fields'])->pluck('handle'))->all();

        $this->assertSame(['custom_pro'], $handles('custom'));
        $this->assertSame(['cache_pro'], $handles('cache'));
        $this->assertSame('section', $tabs['custom']['sections'][0]['fields'][0]['field']['type']);

        $emojiSet = $tabs['general']['sections'][0]['fields'][0]['field'];
        $this->assertSame('statamic-emojis::messages.settings.emoji_set_free', $emojiSet['instructions']);
        $this->assertSame(['native'], array_keys($emojiSet['options']));
    }

    public function test_pro_commands_explain_that_they_need_pro(): void
    {
        $this->artisan('emojis:custom-setup')
            ->expectsOutputToContain('Custom emojis are part of Statamic Emojis Pro.')
            ->assertFailed();
        $this->assertNull(AssetContainer::find('statamic-emojis'));

        $this->artisan('emojis:warm noto')
            ->expectsOutputToContain('Image sets are part of Statamic Emojis Pro.')
            ->assertFailed();

        $this->artisan('emojis:clear')
            ->expectsOutputToContain('Image sets are part of Statamic Emojis Pro.')
            ->assertFailed();

        // The Emojibase data is cached in every edition
        $this->artisan('emojis:clear --data')
            ->expectsOutputToContain('Cleared the cached Emojibase data.')
            ->assertSuccessful();
    }
}
