<?php

namespace StatamicEmojis\Tests;

use StatamicEmojis\CustomEmojis;
use StatamicEmojis\EmojiSets;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Blueprint;
use Statamic\Facades\User;
use Statamic\Fieldtypes\Bard\Augmentor;
use Statamic\Fieldtypes\Bard;
use Statamic\Fields\Field;
use Statamic\Modifiers\Modify;

class CustomEmojisTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('statamic-emojis', ['url' => '/statamic-emojis']);
        config(['statamic-emojis.custom.enabled' => true, 'statamic-emojis.custom.container' => 'statamic-emojis']);
        app(CustomEmojis::class)->clearCache();
    }

    protected function tearDown(): void
    {
        // AddonTestCase stores content in tests/__fixtures__, so remove what the setup command created
        AssetContainer::find('statamic-emojis')?->delete();
        Blueprint::find('assets/statamic-emojis')?->delete();

        parent::tearDown();
    }

    private function custom(): CustomEmojis
    {
        return app(CustomEmojis::class);
    }

    private function setUpContainer(): void
    {
        $this->artisan('emojis:custom-setup')->assertSuccessful();
    }

    private function upload(string $path, array $data = []): void
    {
        Storage::disk('statamic-emojis')->put($path, '<svg xmlns="http://www.w3.org/2000/svg"></svg>');
        AssetContainer::find('statamic-emojis')->makeAsset($path)->data($data)->save();
    }

    public function test_setup_creates_container_and_blueprint_once(): void
    {
        $this->setUpContainer();

        $container = AssetContainer::find('statamic-emojis');
        $this->assertNotNull($container);
        $this->assertSame('statamic-emojis', $container->diskHandle());
        $this->assertSame('Emojis', $container->title());
        $this->assertContains('image:allow_svg', $container->validationRules());

        $blueprint = Blueprint::find('assets/statamic-emojis');
        $this->assertSame(['alt', 'shortcode', 'keywords'], $blueprint->fields()->all()->keys()->all());

        $shortcode = $blueprint->field('shortcode');
        $this->assertSame('statamic-emojis::messages.blueprint.shortcode', $shortcode->display());
        $this->assertSame('Shortcode', __($shortcode->display()));
        $this->assertSame('Suchbegriffe', __($blueprint->field('keywords')->display(), [], 'de'));
        $this->assertStringContainsString(':shortcode:', __($shortcode->instructions()));

        $this->artisan('emojis:custom-setup')
            ->expectsOutputToContain('already exists')
            ->assertSuccessful();
    }

    public function test_upload_rules_accept_svgs_and_reject_other_files(): void
    {
        $this->setUpContainer();
        $rules = AssetContainer::find('statamic-emojis')->validationRules();
        $svg = '<svg width="512" height="512" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M0 0h512v512H0z"/></svg>';

        $passes = fn ($file) => Validator::make(['file' => $file], ['file' => $rules])->passes();

        $this->assertTrue($passes(UploadedFile::fake()->createWithContent('ghost.svg', $svg)));
        $this->assertTrue($passes(UploadedFile::fake()->image('parrot.png', 64, 64)));
        $this->assertFalse($passes(UploadedFile::fake()->createWithContent('notes.txt', 'hello')));
        $this->assertFalse($passes(UploadedFile::fake()->image('huge.png')->size(2048)));
    }

    public function test_it_is_disabled_without_a_container(): void
    {
        $this->assertFalse($this->custom()->isEnabled());
        $this->assertTrue($this->custom()->all()->isEmpty());
    }

    public function test_shortcodes_come_from_the_field_or_the_filename(): void
    {
        $this->setUpContainer();
        $this->upload('Party Parrot.svg', ['alt' => 'Party Parrot', 'keywords' => ['bird', 'dance']]);
        $this->upload('logo.svg', ['shortcode' => 'statamic']);
        $this->upload('duplicate.svg', ['shortcode' => 'statamic']);

        $all = $this->custom()->all();

        $this->assertSame(['party-parrot', 'statamic'], $all->keys()->sort()->values()->all());
        $this->assertSame('Party Parrot', $all['party-parrot']['label']);
        $this->assertSame(['bird', 'dance'], $all['party-parrot']['keywords']);
        $this->assertSame('/statamic-emojis/Party%20Parrot.svg', $all['party-parrot']['url']);
        $this->assertSame('Statamic', $all['statamic']['label']);
        $this->assertSame('/statamic-emojis/duplicate.svg', $all['statamic']['url'], 'first by path wins');
    }

    public function test_emojis_in_subfolders_use_their_filename(): void
    {
        $this->setUpContainer();
        $this->upload('halloween/ghost.svg');

        $this->assertSame('/statamic-emojis/halloween/ghost.svg', $this->custom()->find(':ghost:')['url']);
    }

    public function test_the_list_is_refreshed_when_assets_change(): void
    {
        $this->setUpContainer();
        $this->upload('one.svg');
        $this->assertSame(['one'], $this->custom()->all()->keys()->all());

        $this->upload('two.svg');
        $this->assertSame(['one', 'two'], $this->custom()->all()->keys()->all());
    }

    public function test_replace_turns_known_shortcodes_into_images(): void
    {
        $this->setUpContainer();
        $this->upload('statamic.svg', ['alt' => 'Statamic']);

        $out = $this->custom()->replace('Made with :statamic: at 10:30, not :unknown:');

        $this->assertStringContainsString('<img class="emoji emoji-custom" src="/statamic-emojis/statamic.svg" alt="Statamic" title=":statamic:"', $out);
        $this->assertStringContainsString('at 10:30, not :unknown:', $out);
    }

    public function test_emojis_modifier_handles_custom_emojis_in_every_set(): void
    {
        $this->setUpContainer();
        $this->upload('statamic.svg');

        $native = (string) Modify::value('<p title=":statamic:">I ❤️ :statamic:</p>')->emojis('native');
        $this->assertStringContainsString('<p title=":statamic:">I ❤️ <img class="emoji emoji-custom"', $native);

        $noto = app(EmojiSets::class)->replace('I ❤️ :statamic:', 'noto');
        $this->assertStringContainsString('/sets/noto/2764-fe0f.svg', $noto);
        $this->assertSame(1, substr_count($noto, 'emoji-custom'));
        $this->assertSame(2, substr_count($noto, '<img'));
    }

    public function test_bard_renders_custom_emoji_nodes(): void
    {
        $this->setUpContainer();
        $this->upload('statamic.svg', ['alt' => 'Statamic']);

        $field = (new Field('content', ['type' => 'bard']))->fieldtype();
        $html = (new Augmentor($field))->augment([[
            'type' => 'paragraph',
            'content' => [
                ['type' => 'text', 'text' => 'Hi '],
                ['type' => 'customEmoji', 'attrs' => ['shortcode' => 'statamic']],
                ['type' => 'text', 'text' => ' and '],
                ['type' => 'customEmoji', 'attrs' => ['shortcode' => 'deleted']],
            ],
        ]]);

        $this->assertStringContainsString('<p>Hi <img class="emoji emoji-custom" src="/statamic-emojis/statamic.svg" alt="Statamic"', $html);
        $this->assertStringContainsString(' and :deleted:</p>', $html);
    }

    public function test_cp_route_lists_custom_emojis(): void
    {
        $this->setUpContainer();
        $this->upload('statamic.svg', ['keywords' => ['cms']]);

        $user = User::make()->email('editor@example.com')->makeSuper()->save();

        $this->actingAs($user)
            ->getJson(cp_route('statamic-emojis.custom'))
            ->assertOk()
            ->assertExactJson([[
                'shortcode' => 'statamic',
                'label' => 'Statamic',
                'keywords' => ['cms'],
                'url' => '/statamic-emojis/statamic.svg',
            ]]);
    }
}
