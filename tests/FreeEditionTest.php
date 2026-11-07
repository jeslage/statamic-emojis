<?php

namespace StatamicEmojis\Tests;

use StatamicEmojis\CustomEmojis;
use StatamicEmojis\EmojiSets;
use StatamicEmojis\EmojiSettings;
use StatamicEmojis\Fieldtypes\Emoji;
use StatamicEmojis\SettingsBlueprint;
use Illuminate\Support\Facades\File;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\User;
use Statamic\Modifiers\Modify;

class FreeEditionTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        // Before the addon boots: its settings blueprint is built then
        $app['config']->set('statamic.editions.addons.jeslage/statamic-emojis', 'free');
    }

    protected function tearDown(): void
    {
        app(EmojiSettings::class)->addon()->settings()->set([])->save();
        File::delete(resource_path('addons/statamic-emojis.yaml'));

        parent::tearDown();
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

    public function test_the_settings_page_has_only_the_picker_defaults(): void
    {
        $tabs = SettingsBlueprint::contents()['tabs'];
        $handles = collect($tabs['general']['sections'])->flatMap(fn ($s) => collect($s['fields'])->pluck('handle'));

        $this->assertSame(['general'], array_keys($tabs));
        $this->assertSame('categories', $handles->first());

        $fields = collect($tabs['general']['sections'])->flatMap(fn ($s) => $s['fields'])->keyBy('handle');
        $this->assertSame('hidden', $fields['emoji_set']['field']['type']);
        $this->assertSame('hidden', $fields['custom_emojis']['field']['type']);
    }

    public function test_the_settings_page_links_to_pro(): void
    {
        $this->actingAs(tap(User::make()->email('admin@example.com')->makeSuper())->save())
            ->getJson(cp_route('statamic-emojis.index'))
            ->assertOk()
            ->assertJsonPath('proUrl', EmojiSettings::PRO_URL);
    }

    public function test_saving_in_the_free_edition_keeps_the_pro_settings(): void
    {
        $addon = app(EmojiSettings::class)->addon();
        $addon->settings()->set(['emoji_set' => 'noto', 'custom_emojis' => false, 'skin_tone' => 'dark'])->save();

        $user = tap(User::make()->email('admin@example.com')->makeSuper())->save();
        $page = $this->actingAs($user)->getJson(cp_route('statamic-emojis.index'))->assertOk()->json();

        $this->actingAs($user)
            ->patchJson($page['submitUrl'], [...$page['values'], 'skin_tone' => 'light'])
            ->assertOk();

        $saved = $addon->settings()->raw();
        $this->assertSame('noto', $saved['emoji_set']);
        $this->assertFalse($saved['custom_emojis']);
        $this->assertSame('light', $saved['skin_tone']);
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

        $this->artisan('emojis:clear --data')
            ->expectsOutputToContain('Cleared the cached Emojibase data.')
            ->assertSuccessful();
    }
}
