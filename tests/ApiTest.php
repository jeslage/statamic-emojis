<?php

namespace StatamicEmojis\Tests;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use StatamicEmojis\Values\EmojiTextValue;
use StatamicEmojis\Values\EmojiValue;

class ApiTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('statamic.api.enabled', true);
        $app['config']->set('statamic.api.resources.collections', true);
        $app['config']->set('statamic.graphql.enabled', true);
        $app['config']->set('statamic.graphql.resources.collections', true);
        $app['config']->set('statamic-emojis.set', 'openmoji');
    }

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            '*/en/data.json' => Http::response([
                ['emoji' => '🎉', 'label' => 'party popper', 'group' => 7],
                ['emoji' => '👍', 'label' => 'thumbs up', 'group' => 1, 'skins' => [['emoji' => '👍🏻', 'label' => 'thumbs up: light skin tone']]],
                ['emoji' => '❤️', 'label' => 'red heart', 'group' => 0],
            ]),
            '*' => Http::response('', 404),
        ]);
    }

    protected function tearDown(): void
    {
        Entry::query()->where('collection', 'pages')->get()->each->delete();
        Collection::find('pages')?->delete();
        Blueprint::find('collections.pages.page')?->delete();

        parent::tearDown();
    }

    private function entry(array $data)
    {
        Collection::make('pages')->save();
        Blueprint::make('page')->setNamespace('collections.pages')->setContents(['fields' => [
            ['handle' => 'title', 'field' => ['type' => 'text']],
            ['handle' => 'mood', 'field' => ['type' => 'emoji']],
            ['handle' => 'plain', 'field' => ['type' => 'emoji']],
            ['handle' => 'motto', 'field' => ['type' => 'emoji_text']],
        ]])->save();

        return tap(Entry::make()->collection('pages')->blueprint('page')->slug('test')->data($data))->save();
    }

    public function test_emoji_value_is_a_string_in_templates_and_an_object_in_the_api(): void
    {
        $value = new EmojiValue('👍🏻', 'openmoji');

        $this->assertSame('👍🏻', (string) $value);
        $this->assertSame([
            'value' => '👍🏻',
            'label' => 'thumbs up: light skin tone',
            'url' => 'http://localhost/!/statamic-emojis/sets/openmoji/1f44d-1f3fb.svg',
            'set' => 'openmoji',
            'custom' => false,
            'shortcode' => null,
        ], $value->jsonSerialize());

        // Native set: no image; FE0F differences don't matter for the name
        $native = (new EmojiValue('❤', 'native'))->toArray();
        $this->assertNull($native['url']);
        $this->assertSame('red heart', $native['label']);
    }

    public function test_emoji_text_value_has_html_with_images(): void
    {
        $value = new EmojiTextValue('Hi <b>🎉</b>', 'noto');

        $this->assertSame('Hi <b>🎉</b>', (string) $value);
        $this->assertStringStartsWith('Hi &lt;b&gt;<img class="emoji"', $value->toArray()['html']);
        $this->assertStringContainsString('/sets/noto/1f389.svg', $value->toArray()['html']);
    }

    public function test_antlers_output_is_unchanged_and_extras_are_available(): void
    {
        $entry = $this->entry(['title' => 'Test', 'mood' => '🎉', 'motto' => 'Done 👍']);

        View::addLocation(__DIR__.'/views');
        file_put_contents(__DIR__.'/views/api-test.antlers.html', '{{ mood }}|{{ mood:label }}|{{ if mood }}yes{{ /if }}|{{ if plain }}yes{{ else }}no{{ /if }}|{{ motto }}');

        try {
            $out = trim(view('api-test', $entry->toAugmentedArray())->render());
        } finally {
            unlink(__DIR__.'/views/api-test.antlers.html');
        }

        $this->assertSame('🎉|party popper|yes|no|Done 👍', $out);
        $this->assertSame('🎉', (string) $entry->mood);
    }

    public function test_rest_api_returns_emoji_objects(): void
    {
        $entry = $this->entry(['title' => 'Test', 'mood' => '🎉', 'motto' => 'Done 👍']);

        $data = $this->getJson("/api/collections/pages/entries/{$entry->id()}")->assertOk()->json('data');

        $this->assertSame('🎉', $data['mood']['value']);
        $this->assertSame('party popper', $data['mood']['label']);
        $this->assertSame('openmoji', $data['mood']['set']);
        $this->assertStringEndsWith('/!/statamic-emojis/sets/openmoji/1f389.svg', $data['mood']['url']);
        $this->assertNull($data['plain']);
        $this->assertSame('Done 👍', $data['motto']['value']);
        $this->assertStringContainsString('/sets/openmoji/1f44d.svg', $data['motto']['html']);
    }

    public function test_graphql_returns_emoji_values(): void
    {
        $entry = $this->entry(['title' => 'Test', 'mood' => '👍🏻', 'motto' => 'Done 👍']);

        $query = <<<GQL
            {
                entry(id: "{$entry->id()}") {
                    ... on Entry_Pages_Page {
                        mood { value label url set custom shortcode }
                        motto { value html set }
                    }
                }
            }
        GQL;

        $response = $this->postJson('/graphql', ['query' => $query])->assertOk();

        $this->assertNull($response->json('errors'), json_encode($response->json('errors')));
        $this->assertSame([
            'value' => '👍🏻',
            'label' => 'thumbs up: light skin tone',
            'url' => 'http://localhost/!/statamic-emojis/sets/openmoji/1f44d-1f3fb.svg',
            'set' => 'openmoji',
            'custom' => false,
            'shortcode' => null,
        ], $response->json('data.entry.mood'));
        $this->assertSame('openmoji', $response->json('data.entry.motto.set'));
    }
}
