<?php

namespace Convoy\Emojis\Http\Controllers;

use Convoy\Emojis\EmojiSets;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves emoji images of a set from the addon's cache (fetched from upstream on first use).
 */
class EmojiImageController
{
    public function __invoke(EmojiSets $sets, string $set, string $codes): Response
    {
        abort_unless($sets->isImageSet($set), 404);

        $svg = $sets->svg($set, $codes);

        // Emojis the set doesn't have (or a failed fetch) fall back to the native character, so there's
        // no broken image. Browsers only keep the fallback briefly, in case it was a network error.
        $cacheControl = $svg === null ? 'public, max-age=3600' : 'public, max-age=2592000';
        $svg ??= $sets->fallbackSvg($codes);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => $cacheControl,
            'X-Content-Type-Options' => 'nosniff',
            // Third-party SVGs must not run scripts if opened directly on this domain.
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'",
        ]);
    }
}
