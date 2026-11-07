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
    | "set" is the site-wide set (the Emojis page in the CP overrides it);
    | all fields use it.
    |
    | Built-in sets: noto, fluent, openmoji. Licenses: Noto (Apache 2.0),
    | Fluent (MIT), OpenMoji (CC BY-SA 4.0, attribution required:
    | "Emoji graphics by OpenMoji, https://openmoji.org").
    |
    */

    'set' => env('STATAMIC_EMOJIS_SET', 'native'),

    /*
    | Add your own image sets here; they're added to the built-in ones. An entry
    | with a built-in name changes only the keys you set (e.g. a self-hosted url).
    | Names: lowercase letters only.
    |
    | "naming" is how the set names its files:
    |   "fluent"   1f9d1-200d-1f680.svg      (lowercase, joined by "-"; default)
    |   "openmoji" 1F9D1-200D-1F680.svg      (uppercase, joined by "-")
    |   "noto"     2D/svg/emoji_u1f9d1_200d_1f680.svg (Noto's repository layout)
    |   or a class implementing StatamicEmojis\Contracts\SetNaming.
    | Variants with and without the variation selector FE0F are tried.
    |
    | Sets can also be added in code: StatamicEmojis\EmojiSets::extend('name', [...]).
    */

    'sets' => [
        // 'twemoji' => [
        //     'label' => 'Twemoji',
        //     'url' => 'https://cdn.jsdelivr.net/gh/jdecked/twemoji@15.1.0/assets/svg',
        //     'naming' => 'fluent',
        // ],
    ],

    /*
    | Image sets to remove from the selection, e.g. ['fluent'], or ['*'] for
    | native emojis only.
    */

    'disabled_sets' => [],

    'images' => [
        'cache_path' => storage_path('statamic-emojis/sets'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom emojis
    |--------------------------------------------------------------------------
    |
    | Editors upload their own emoji images to an asset container and use them
    | by shortcode, e.g. ":party-parrot:". Run "php please emojis:custom-setup"
    | once to create the container and its blueprint.
    |
    | "disk" is defined by the addon (public/statamic-emojis) unless your
    | config/filesystems.php already has a disk with that name, e.g. on S3.
    | Container and disk are prefixed to avoid clashes with a site's own ones.
    |
    */

    'custom' => [
        'enabled' => env('STATAMIC_EMOJIS_CUSTOM', true),
        'container' => env('STATAMIC_EMOJIS_CUSTOM_CONTAINER', 'statamic-emojis'),
        'disk' => env('STATAMIC_EMOJIS_CUSTOM_DISK', 'statamic-emojis'),
    ],

];
