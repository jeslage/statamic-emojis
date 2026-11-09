<?php

namespace StatamicEmojis\Tests;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use StatamicEmojis\Emojibase;

class EmojibaseTest extends TestCase
{
    public function test_a_failed_fetch_is_not_retried_right_away(): void
    {
        $calls = 0;
        Http::fake(['*' => function () use (&$calls) {
            $calls++;

            throw new ConnectionException('jsDelivr is unreachable');
        }]);

        $emojibase = app(Emojibase::class);

        foreach ([1, 2, 3] as $_) {
            try {
                $emojibase->data('en');
                $this->fail('Expected the fetch to fail.');
            } catch (\Throwable) {
            }
        }

        $this->assertSame(1, $calls);
    }

    public function test_a_failed_fetch_is_retried_after_a_while(): void
    {
        Http::fake(['*' => Http::sequence()
            ->push('', 503)
            ->push([['emoji' => '🎉', 'label' => 'party popper']]),
        ]);

        $emojibase = app(Emojibase::class);

        try {
            $emojibase->data('en');
        } catch (\Throwable) {
        }

        $this->travel(Emojibase::FAILURE_TTL + 1)->seconds();

        $this->assertSame('party popper', $emojibase->labels('en')['🎉']);
    }

    public function test_clearing_forgets_failed_fetches(): void
    {
        Http::fake(['*' => Http::sequence()
            ->push('', 503)
            ->push([['emoji' => '🎉', 'label' => 'party popper']]),
        ]);

        $emojibase = app(Emojibase::class);

        try {
            $emojibase->data('en');
        } catch (\Throwable) {
        }

        $emojibase->clear();

        $this->assertSame('party popper', $emojibase->labels('en')['🎉']);
    }
}
