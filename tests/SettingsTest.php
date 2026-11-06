<?php

namespace Convoy\Emojis\Tests;

use Convoy\Emojis\EmojiSets;
use Convoy\Emojis\EmojiSettings;
use Convoy\Emojis\Fieldtypes\Emoji;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Statamic\Facades\User;

class SettingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['statamic-emojis.images.cache_path' => $this->cachePath()]);
        File::deleteDirectory($this->cachePath());
    }

    protected function tearDown(): void
    {
        $this->settings()->addon()->settings()->set([])->save();
        File::delete(resource_path('addons/statamic-emojis.yaml'));
        File::deleteDirectory($this->cachePath());

        parent::tearDown();
    }

    private function cachePath(): string
    {
        return sys_get_temp_dir().'/statamic-emojis-settings-tests';
    }

    private function settings(): EmojiSettings
    {
        return app(EmojiSettings::class);
    }

    private function save(array $values): void
    {
        $this->settings()->addon()->settings()->set($values)->save();
    }

    private function superUser()
    {
        return tap(User::make()->email('admin@example.com')->makeSuper())->save();
    }

    public function test_saved_settings_override_the_config(): void
    {
        config(['statamic-emojis.set' => 'noto', 'statamic-emojis.custom.enabled' => true]);

        $this->assertSame('noto', app(EmojiSets::class)->default());
        $this->assertTrue($this->settings()->customEmojis());

        $this->save(['emoji_set' => 'openmoji', 'custom_emojis' => false, 'categories' => ['flags'], 'skin_tone' => 'dark']);

        $this->assertSame('openmoji', app(EmojiSets::class)->default());
        $this->assertFalse($this->settings()->customEmojis());
        $this->assertSame(['flags'], $this->settings()->categories());
        $this->assertSame('dark', $this->settings()->skinTone());
        $this->assertTrue($this->settings()->skinToneSelector());
    }

    public function test_fields_show_the_site_wide_values_as_defaults(): void
    {
        $this->save(['emoji_set' => 'fluent', 'skin_tone' => 'medium', 'skin_tone_selector' => false]);

        $fields = collect((new Emoji)->configFields()->all());

        $this->assertSame('Default (Fluent)', $fields['emoji_set']->get('placeholder'));
        $this->assertSame('Default (Medium)', $fields['skin_tone']->get('placeholder'));
        $this->assertSame('Default (Hide)', $fields['skin_tone_selector']->get('placeholder'));
        $this->assertSame(['show' => 'Show', 'hide' => 'Hide'], $fields['custom_emojis']->get('options'));
    }

    public function test_settings_page_uses_translation_keys(): void
    {
        // Built while the addon boots (before the user's locale is set), so the CP translates the keys
        $blueprint = $this->settings()->addon()->settingsBlueprint();
        $emojiSet = $blueprint->field('emoji_set');

        $this->assertSame('statamic-emojis::messages.config.emoji_set', $emojiSet->display());
        $this->assertSame('statamic-emojis::messages.settings.emoji_set_instructions', $emojiSet->instructions());
        $this->assertSame('statamic-emojis::messages.sets.noto', $emojiSet->get('options')['noto']);
        $this->assertSame('Emoji-Set', __($emojiSet->display(), [], 'de'));
    }

    public function test_show_hide_values_resolve_with_a_fallback(): void
    {
        $this->assertTrue(EmojiSettings::bool('show', false));
        $this->assertFalse(EmojiSettings::bool('hide', true));
        $this->assertFalse(EmojiSettings::bool(false, true));
        $this->assertTrue(EmojiSettings::bool(null, true));
    }

    public function test_emojis_page_shows_the_settings_form(): void
    {
        $this->actingAs($this->superUser())
            ->getJson(cp_route('statamic-emojis.index'))
            ->assertOk()
            ->assertJsonPath('title', 'Emojis')
            ->assertJsonPath('submitUrl', cp_route('addons.settings.update', 'statamic-emojis'))
            ->assertJsonPath('blueprint.tabs.0.handle', 'general');
    }

    public function test_emojis_page_requires_the_settings_permission(): void
    {
        $editor = tap(User::make()->email('editor@example.com'))->save();

        $this->actingAs($editor)->getJson(cp_route('statamic-emojis.index'))->assertForbidden();
        $this->actingAs($editor)->postJson(cp_route('statamic-emojis.cache.clear', 'noto'))->assertForbidden();
    }

    public function test_cache_tools_fill_report_and_clear_a_set(): void
    {
        Http::fake([
            '*/emojibase-data*/en/data.json' => Http::response([
                ['emoji' => '😀', 'group' => 0, 'label' => 'grinning face'],
                ['emoji' => '👍', 'group' => 1, 'label' => 'thumbs up', 'skins' => [['emoji' => '👍🏻']]],
            ]),
            '*/1f600.svg' => Http::response('<svg id="grin"></svg>'),
            '*/1f44d.svg' => Http::response('<svg id="thumb"></svg>'),
            '*' => Http::response('', 404),
        ]);

        $this->actingAs($this->superUser());

        $this->postJson(cp_route('statamic-emojis.cache.warm', 'fluent'), ['offset' => 0, 'limit' => 2])
            ->assertOk()
            ->assertJson(['next' => 2, 'total' => 3, 'done' => false, 'failed' => 0]);

        $this->postJson(cp_route('statamic-emojis.cache.warm', 'fluent'), ['offset' => 2, 'limit' => 2])
            ->assertOk()
            ->assertJson(['next' => 3, 'total' => 3, 'done' => true, 'failed' => 1]); // 👍🏻 isn't in the fake set

        $fluent = collect($this->getJson(cp_route('statamic-emojis.cache.status'))->assertOk()->json())->firstWhere('set', 'fluent');
        $this->assertSame(2, $fluent['cached']);
        $this->assertSame(3, $fluent['total']);

        $this->postJson(cp_route('statamic-emojis.cache.clear', 'fluent'))->assertOk()->assertJson(['deleted' => 2]);
        $this->postJson(cp_route('statamic-emojis.cache.warm', 'native'))->assertNotFound();
    }
}
