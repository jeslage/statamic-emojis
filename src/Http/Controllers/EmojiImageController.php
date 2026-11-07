<?php

namespace StatamicEmojis\Http\Controllers;

use StatamicEmojis\EmojiSets;
use Symfony\Component\HttpFoundation\Response;

class EmojiImageController
{
    public function __invoke(EmojiSets $sets, string $set, string $codes): Response
    {
        abort_unless($sets->isImageSet($set), 404);

        $svg = $sets->svg($set, $codes);

        // Missing emojis fall back to the native character; browsers keep it only briefly, in case it was a network error.
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
