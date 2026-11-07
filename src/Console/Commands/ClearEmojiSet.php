<?php

namespace StatamicEmojis\Console\Commands;

use StatamicEmojis\Emojibase;
use StatamicEmojis\EmojiSets;
use StatamicEmojis\EmojiSettings;
use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;

/**
 * Empties the image cache of emoji sets, e.g. after changing a set's URL in the config.
 * Images are fetched again on next use (or with emojis:warm).
 */
class ClearEmojiSet extends Command
{
    use RunsInPlease;

    protected $signature = 'emojis:clear
        {set? : Image set to clear (default: all image sets)}
        {--data : Also forget the cached Emojibase data (emoji names, categories, search keywords)}';

    protected $description = 'Clear the cached images of an emoji set';

    public function handle(EmojiSets $sets, Emojibase $emojibase): int
    {
        // Image sets are part of Pro; the Emojibase data (--data) is cached in every edition
        if (! app(EmojiSettings::class)->pro()) {
            if (! $this->option('data') || $this->argument('set') !== null) {
                $this->components->error('Image sets are part of Statamic Emojis Pro.');

                return self::FAILURE;
            }

            $emojibase->clear();
            $this->components->info('Cleared the cached Emojibase data.');

            return self::SUCCESS;
        }

        $imageSets = array_values(array_diff($sets->sets(), [EmojiSets::NATIVE]));
        $set = $this->argument('set');

        if ($set !== null && ! $sets->isImageSet($set)) {
            $this->components->error("[{$set}] is not an image set. Available: ".implode(', ', $imageSets));

            return self::FAILURE;
        }

        foreach ($set ? [$set] : $imageSets as $clear) {
            $count = $sets->clear($clear);
            $this->components->info("Cleared [{$clear}]: {$count} cached images deleted.");
        }

        if ($this->option('data')) {
            $emojibase->clear();
            $this->components->info('Cleared the cached Emojibase data.');
        }

        $this->components->bulletList([
            'Images are fetched again on first view, or run `php please emojis:warm {set}`.',
            'Browsers may keep images they already loaded for up to 30 days.',
        ]);

        return self::SUCCESS;
    }
}
