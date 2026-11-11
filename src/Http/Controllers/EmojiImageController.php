<?php

namespace StatamicEmojis\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Statamic\Facades\User;
use StatamicEmojis\EmojiSets;
use Symfony\Component\HttpFoundation\Response;

class EmojiImageController
{
    public function __invoke(Request $request, EmojiSets $sets, string $set, string $codes): Response
    {
        abort_unless($sets->isImageSet($set), 404);

        // Only emojis Emojibase knows are fetched, each stored once under its canonical code.
        $canonical = $sets->canonical($codes);
        $svg = $canonical === null ? null : $this->svg($request, $sets, $set, $canonical);

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

    // Cached images are always served; fetches from the set's CDN are limited per logged-out visitor.
    // Logged-in users aren't limited, as the CP's emoji picker requests every image of a set at once.
    protected function svg(Request $request, EmojiSets $sets, string $set, string $codes): ?string
    {
        if ($sets->isCached($set, $codes) || User::current() !== null) {
            return $sets->svg($set, $codes);
        }

        $key = 'statamic-emojis:sets:fetch:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, EmojiSets::FETCH_LIMIT)) {
            return null;
        }

        RateLimiter::hit($key);

        return $sets->svg($set, $codes);
    }
}
