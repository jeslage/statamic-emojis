<?php

namespace StatamicEmojis\Http\Controllers;

use StatamicEmojis\Emojibase;
use StatamicEmojis\EmojiSets;
use StatamicEmojis\EmojiSettings;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;

/**
 * Image cache tools on the Emojis page. Warming runs in chunks driven by the browser,
 * so it works without a queue worker and never hits request timeouts.
 */
class ImageCacheController extends CpController
{
    public function status(EmojiSettings $settings, EmojiSets $sets)
    {
        $this->authorize('editSettings', $settings->addon());

        return $sets->cacheStatus();
    }

    public function warm(Request $request, EmojiSettings $settings, EmojiSets $sets, Emojibase $emojibase, string $set)
    {
        $this->authorize('editSettings', $settings->addon());
        abort_unless($sets->isImageSet($set), 404);

        $codes = $sets->allCodes($emojibase);
        $offset = max(0, (int) $request->input('offset', 0));
        $limit = min(100, max(1, (int) $request->input('limit', 50)));

        $failed = $sets->warm($set, array_slice($codes, $offset, $limit));
        $next = min($offset + $limit, count($codes));

        return [
            'next' => $next,
            'total' => count($codes),
            'done' => $next >= count($codes),
            'failed' => count($failed),
        ];
    }

    public function clear(EmojiSettings $settings, EmojiSets $sets, string $set)
    {
        $this->authorize('editSettings', $settings->addon());
        abort_unless($sets->isImageSet($set), 404);

        return ['deleted' => $sets->clear($set)];
    }
}
