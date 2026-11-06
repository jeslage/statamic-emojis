<?php

namespace Convoy\Emojis\Http\Controllers;

use Convoy\Emojis\Emojibase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves Emojibase data to the picker, filtered by the field's allowed categories.
 *
 * vue-frimousse requests "{emojibaseUrl}/{locale}/data.json" and ".../messages.json",
 * so the categories are part of the base URL: "all" or a comma-separated list of keys.
 */
class EmojibaseController
{
    public function __invoke(Request $request, Emojibase $emojibase, string $categories, string $locale, string $file): Response
    {
        abort_unless(Emojibase::isLocale($locale), 404);

        $categories = $categories === 'all' ? [] : explode(',', $categories);
        abort_if(array_diff($categories, array_keys(Emojibase::CATEGORIES)), 404);

        $data = $file === 'data.json'
            ? $emojibase->data($locale, $categories)
            : $emojibase->messages($locale);

        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // frimousse compares ETags (HEAD requests) to decide whether its browser cache is still valid.
        $etag = '"'.md5($json).'"';

        if ($request->headers->get('If-None-Match') === $etag) {
            return response('', 304, ['ETag' => $etag]);
        }

        return response($json, 200, [
            'Content-Type' => 'application/json; charset=utf-8',
            'ETag' => $etag,
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
