<?php

namespace StatamicEmojis\Tests;

use StatamicEmojis\EmojiSets;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\DataProvider;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Modifiers\Modify;

class EmojiSetsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['statamic-emojis.images.cache_path' => $this->cachePath()]);
        File::deleteDirectory($this->cachePath());
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->cachePath());

        parent::tearDown();
    }

    private function cachePath(): string
    {
        return sys_get_temp_dir().'/statamic-emojis-tests';
    }

    private function sets(): EmojiSets
    {
        return app(EmojiSets::class);
    }

    public function test_codepoints_are_fully_qualified(): void
    {
        $this->assertSame('2764-fe0f', $this->sets()->codepoints('❤️'));
        $this->assertSame('1f44d-1f3fb', $this->sets()->codepoints('👍🏻'));
        $this->assertSame('1f9d1-200d-1f680', $this->sets()->codepoints('🧑‍🚀'));
        $this->assertSame('1f1e9-1f1ea', $this->sets()->codepoints('🇩🇪'));
    }

    public static function candidates(): array
    {
        return [
            'noto heart' => ['noto', '2764-fe0f', '2D/svg/emoji_u2764.svg'],
            'noto skin tone' => ['noto', '1f44d-1f3fb', '2D/svg/emoji_u1f44d_1f3fb.svg'],
            'noto zwj' => ['noto', '1f3f3-fe0f-200d-1f308', '2D/svg/emoji_u1f3f3_200d_1f308.svg'],
            'noto flag' => ['noto', '1f1e9-1f1ea', 'third_party/region-flags/waved-svg/emoji_u1f1e9_1f1ea.svg'],
            'fluent heart' => ['fluent', '2764-fe0f', '2764-fe0f.svg'],
            'fluent keycap' => ['fluent', '2a-fe0f-20e3', '002a-fe0f-20e3.svg'],
            'openmoji heart' => ['openmoji', '2764-fe0f', '2764.svg'],
            'openmoji smiling face' => ['openmoji', '263a-fe0f', '263A.svg'],
            'openmoji zwj keeps inner fe0f' => ['openmoji', '1f3f3-fe0f-200d-1f308', '1F3F3-FE0F-200D-1F308.svg'],
        ];
    }

    #[DataProvider('candidates')]
    public function test_candidates_follow_each_sets_naming(string $set, string $codes, string $expected): void
    {
        $this->assertContains(
            strtolower($expected),
            array_map('strtolower', $this->sets()->candidates($set, $codes)),
        );
    }

    public static function exactCandidates(): array
    {
        return [
            'fluent keycap' => ['fluent', '31-fe0f-20e3', [
                '0031-fe0f-20e3.svg', '0031-20e3.svg', '31-fe0f-20e3.svg', '31-20e3.svg',
            ]],
            'openmoji keycap' => ['openmoji', '31-fe0f-20e3', [
                '0031-FE0F-20E3.svg', '0031-20E3.svg', '31-FE0F-20E3.svg', '31-20E3.svg',
            ]],
            'noto subdivision flag' => ['noto', '1f3f4-e0067-e0062-e0073-e0063-e0074-e007f', [
                'third_party/region-flags/waved-svg/emoji_u1f3f4_e0067_e0062_e0073_e0063_e0074_e007f.svg',
            ]],
            'noto zwj (unchanged)' => ['noto', '1f3f3-fe0f-200d-1f308', [
                '2D/svg/emoji_u1f3f3_fe0f_200d_1f308.svg',
                '2D/svg/emoji_u1f3f3_200d_1f308.svg',
            ]],
        ];
    }

    #[DataProvider('exactCandidates')]
    public function test_candidates_pad_codepoints_then_fall_back_to_unpadded(string $set, string $codes, array $expected): void
    {
        $this->assertSame($expected, $this->sets()->candidates($set, $codes));
    }

    public function test_keycaps_are_fetched_with_padded_codepoints(): void
    {
        Http::fake([
            'cdn.jsdelivr.net/npm/openmoji@17.0.0/color/svg/0031-FE0F-20E3.svg' => Http::response('<svg id="one"></svg>'),
            '*' => Http::response('', 404),
        ]);

        $this->assertSame('<svg id="one"></svg>', $this->sets()->svg('openmoji', '31-fe0f-20e3'));
        $this->assertFileExists($this->cachePath().'/openmoji/31-fe0f-20e3.svg');
    }

    public function test_resolve_uses_given_set_then_site_set(): void
    {
        config(['statamic-emojis.set' => 'noto']);

        $this->assertSame('openmoji', $this->sets()->resolve('openmoji'));
        $this->assertSame('native', $this->sets()->resolve('native'));
        $this->assertSame('noto', $this->sets()->resolve(null));
        $this->assertSame('noto', $this->sets()->resolve('apple'));

        config(['statamic-emojis.set' => 'unknown']);
        $this->assertSame('native', $this->sets()->resolve(null));
    }

    public function test_replace_turns_emojis_in_text_into_images(): void
    {
        $html = $this->sets()->replace('Hi 👋🏽 from 🇩🇪 and 🧑‍🚀!', 'noto');

        $this->assertSame(3, substr_count($html, '<img class="emoji"'));
        $this->assertStringContainsString('/!/statamic-emojis/sets/noto/1f44b-1f3fd.svg', $html);
        $this->assertStringContainsString('/!/statamic-emojis/sets/noto/1f1e9-1f1ea.svg', $html);
        $this->assertStringContainsString('/!/statamic-emojis/sets/noto/1f9d1-200d-1f680.svg', $html);
        $this->assertStringContainsString('alt="👋🏽"', $html);
        $this->assertStringStartsWith('Hi <img', $html);
    }

    public function test_replace_keeps_subdivision_flags_whole(): void
    {
        $html = $this->sets()->replace('🏴󠁧󠁢󠁳󠁣󠁴󠁿!', 'noto');

        $this->assertSame(1, substr_count($html, '<img'));
        $this->assertStringContainsString('/sets/noto/1f3f4-e0067-e0062-e0073-e0063-e0074-e007f.svg', $html);
        $this->assertStringEndsWith('>!', $html);
    }

    public function test_replace_leaves_tags_and_attributes_alone(): void
    {
        $html = '<p title="Party 🎉"><a href="/x">Party 🎉</a></p>';
        $out = $this->sets()->replace($html, 'openmoji');

        $this->assertStringContainsString('<p title="Party 🎉">', $out);
        $this->assertSame(1, substr_count($out, '<img'));
    }

    public function test_replace_is_a_no_op_for_native(): void
    {
        $this->assertSame('Hi 👋', $this->sets()->replace('Hi 👋', 'native'));
    }

    public function test_image_route_fetches_caches_and_serves_svgs(): void
    {
        Http::fake([
            '*/openmoji@*/color/svg/2764.svg' => Http::response('<svg id="heart"></svg>'),
            '*' => Http::response('', 404),
        ]);

        $response = $this->get('/!/statamic-emojis/sets/openmoji/2764-fe0f.svg');

        $response->assertOk();
        $this->assertSame('<svg id="heart"></svg>', $response->getContent());
        $this->assertStringStartsWith('image/svg+xml', $response->headers->get('Content-Type'));
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertStringContainsString("default-src 'none'", $response->headers->get('Content-Security-Policy'));
        $this->assertFileExists($this->cachePath().'/openmoji/2764-fe0f.svg');

        Http::assertSentCount(2);
        $this->get('/!/statamic-emojis/sets/openmoji/2764-fe0f.svg')->assertOk();
        Http::assertSentCount(2);
    }

    public function test_image_route_falls_back_to_the_native_character(): void
    {
        Http::fake(['*' => Http::response('', 404)]);

        $response = $this->get('/!/statamic-emojis/sets/fluent/1f600.svg');

        $response->assertOk();
        $this->assertStringContainsString('😀', $response->getContent());
        $this->assertStringContainsString('<text', $response->getContent());
    }

    public function test_network_errors_fall_back_briefly_and_are_retried(): void
    {
        $calls = 0;
        Http::fake(['*' => function () use (&$calls) {
            if ($calls++ === 0) {
                throw new \Illuminate\Http\Client\ConnectionException('down');
            }

            return Http::response('<svg id="grin"></svg>');
        }]);

        $response = $this->get('/!/statamic-emojis/sets/noto/1f600.svg');
        $response->assertOk();
        $this->assertStringContainsString('<text', $response->getContent());
        $this->assertStringContainsString('max-age=3600', $response->headers->get('Cache-Control'));

        $this->assertSame('<svg id="grin"></svg>', $this->get('/!/statamic-emojis/sets/noto/1f600.svg')->getContent());
    }

    public function test_warm_fetches_concurrently_and_reports_failures(): void
    {
        Http::fake([
            '*/1f600.svg' => Http::response('<svg id="grin"></svg>'),
            '*' => Http::response('', 404),
        ]);

        $failed = $this->sets()->warm('fluent', ['1f600', '1f9ff']);

        $this->assertSame(['1f9ff'], $failed);
        $this->assertTrue($this->sets()->isCached('fluent', '1f600'));
    }

    public function test_clear_deletes_images_and_forgets_misses(): void
    {
        Http::fake([
            '*/1f600.svg' => Http::response('<svg id="grin"></svg>'),
            '*' => Http::response('', 404),
        ]);

        $this->sets()->svg('fluent', '1f600');
        $this->assertNull($this->sets()->svg('fluent', '1f9ff'));
        $this->assertTrue($this->sets()->isCached('fluent', '1f600'));

        $this->artisan('emojis:clear', ['set' => 'fluent'])
            ->expectsOutputToContain('1 cached images deleted')
            ->assertSuccessful();

        $this->assertFalse($this->sets()->isCached('fluent', '1f600'));

        Http::fake(['*' => Http::response('<svg id="nazar"></svg>')]);
        $sent = count(Http::recorded());
        $this->sets()->svg('fluent', '1f9ff');
        $this->assertGreaterThan($sent, count(Http::recorded()));
    }

    public function test_clear_rejects_unknown_sets(): void
    {
        $this->artisan('emojis:clear', ['set' => 'native'])->assertFailed();
    }

    public function test_image_route_rejects_unknown_sets_and_bad_codes(): void
    {
        Http::fake();

        $this->get('/!/statamic-emojis/sets/native/1f600.svg')->assertNotFound();
        $this->get('/!/statamic-emojis/sets/apple/1f600.svg')->assertNotFound();
        $this->get('/!/statamic-emojis/sets/noto/..%2F..%2Fetc.svg')->assertNotFound();
        $this->get('/!/statamic-emojis/sets/noto/1f600zz.svg')->assertNotFound();

        Http::assertNothingSent();
    }

    public function test_modifier_uses_parameter_or_site_set(): void
    {
        config(['statamic-emojis.set' => 'fluent']);

        $this->assertStringContainsString('/sets/fluent/1f389.svg', (string) Modify::value('🎉')->emojis());
        $this->assertStringContainsString('/sets/noto/1f389.svg', (string) Modify::value('🎉')->emojis('noto'));
        $this->assertSame('🎉', (string) Modify::value('🎉')->emojis('native'));
    }

    private function emojiValue(string $emoji, string $type = 'emoji'): Value
    {
        $field = new Field('field', ['type' => $type]);

        return new Value($emoji, 'field', $field->fieldtype());
    }

    // Rendered like a site template, as Antlers::parse treats input as user content
    private function renderAntlers(array $data): string
    {
        View::addLocation(__DIR__.'/views');

        return trim(view('emojis-modifier', $data + ['mood' => null, 'other' => null, 'a' => null])->render());
    }

    public function test_field_values_use_the_site_set(): void
    {
        config(['statamic-emojis.set' => 'openmoji']);

        $this->assertStringContainsString('/sets/openmoji/1f389.svg', (string) Modify::value($this->emojiValue('🎉'))->emojis());

        [$mood, $other] = explode('|', $this->renderAntlers([
            'mood' => $this->emojiValue('🎉'),
            'other' => $this->emojiValue('👍', 'emoji_text'),
        ]));

        $this->assertStringContainsString('/sets/openmoji/1f389.svg', $mood);
        $this->assertStringContainsString('/sets/openmoji/1f44d.svg', $other);
    }
}
