<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Emojibase data
    |--------------------------------------------------------------------------
    |
    | The picker loads its emoji data (labels, categories, search keywords,
    | skin tones) from Emojibase through the Control Panel, which filters it
    | by the field's allowed categories and caches it.
    |
    | "url" is where the server fetches the data from: any host serving the
    | emojibase-data package as "{url}/{locale}/data.json". Point it at a
    | self-hosted copy if the server can't reach the CDN.
    |
    */

    'emojibase' => [
        'url' => env('STATAMIC_EMOJIS_EMOJIBASE_URL', 'https://cdn.jsdelivr.net/npm/emojibase-data@18'),
        'cache_ttl' => (int) env('STATAMIC_EMOJIS_CACHE_TTL', 60 * 60 * 24 * 7),
    ],

];
