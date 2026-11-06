<?php

namespace StatamicEmojis\Console\Commands;

use StatamicEmojis\Emojibase;
use StatamicEmojis\EmojiSets;
use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;

/**
 * Fetches every emoji (including skin tone variants) of an image set into the cache,
 * so editors and visitors never wait for the first upstream fetch.
 */
class WarmEmojiSet extends Command
{
    use RunsInPlease;

    protected $signature = 'emojis:warm {set? : Image set to fetch (default: the site-wide set)} {--concurrency=10}';

    protected $description = 'Fetch all images of an emoji set into the cache';

    public function handle(EmojiSets $sets, Emojibase $emojibase): int
    {
        $set = $this->argument('set') ?? $sets->default();

        if (! $sets->isImageSet($set)) {
            $this->components->error("[{$set}] is not an image set. Available: ".implode(', ', array_diff($sets->sets(), [EmojiSets::NATIVE])));

            return self::FAILURE;
        }

        $codes = collect($sets->allCodes($emojibase));

        $this->components->info("Fetching {$codes->count()} emojis of [{$set}]…");

        $bar = $this->output->createProgressBar($codes->count());
        $failed = [];

        foreach ($codes->chunk(max(1, (int) $this->option('concurrency'))) as $chunk) {
            $failed = [...$failed, ...$sets->warm($set, $chunk->all())];
            $bar->advance($chunk->count());
        }

        $bar->finish();
        $this->newLine(2);

        $fetched = $codes->count() - count($failed);
        $this->components->info("{$fetched} of {$codes->count()} emojis of [{$set}] are cached.");

        if ($failed) {
            $this->components->warn(count($failed).' emojis use the native fallback: they aren\'t in the set, or a network error occurred. Run the command again to retry network errors.');
        }

        return self::SUCCESS;
    }
}
