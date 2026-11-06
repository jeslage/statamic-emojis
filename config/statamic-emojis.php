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

    /*
    |--------------------------------------------------------------------------
    | Emoji set
    |--------------------------------------------------------------------------
    |
    | How emojis look in the picker and in the front-end output of the
    | "emoji_images" modifier. "native" uses each device's own emoji font;
    | the other sets are SVG images, fetched once by the server, cached in
    | "cache_path" and served from your own domain (no visitor IPs go to a CDN).
    | Fields can override the set in their "Emoji Picker" settings.
    |
    | Licenses: Noto (Apache 2.0), Fluent (MIT), OpenMoji (CC BY-SA 4.0,
    | attribution required: "Emoji graphics by OpenMoji, https://openmoji.org").
    |
    */

    'set' => env('STATAMIC_EMOJIS_SET', 'native'),

    'sets' => [
        'noto' => [
            'url' => 'https://cdn.jsdelivr.net/gh/googlefonts/noto-emoji@e20cbc2bbec1926686be9f9bee7d1d2cfa1fea0e',
            'naming' => 'noto',
        ],
        'fluent' => [
            'url' => 'https://cdn.jsdelivr.net/npm/@lobehub/fluent-emoji-flat@1.1.0/assets',
            'naming' => 'fluent',
        ],
        'openmoji' => [
            'url' => 'https://cdn.jsdelivr.net/npm/openmoji@17.0.0/color/svg',
            'naming' => 'openmoji',
        ],
    ],

    'images' => [
        'cache_path' => storage_path('statamic-emojis/sets'),
    ],

];
