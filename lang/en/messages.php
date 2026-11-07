<?php

return [
    'emoji' => 'Emoji',
    'insert_emoji' => 'Insert emoji',
    'search' => 'Search…',
    'search_tab' => 'Search',
    'recent_tab' => 'Recently used',
    'loading' => 'Loading…',
    'no_results' => 'No emoji found.',
    'pick_an_emoji' => 'Pick an emoji…',
    'select_emoji' => 'Select emoji',
    'change_emoji' => 'Change emoji',
    'remove_emoji' => 'Remove emoji',
    'change_skin_tone' => 'Change skin tone',

    'fieldtypes' => [
        'emoji' => 'Emoji',
        'emoji_text' => 'Emoji Text',
    ],

    'categories' => [
        'smileys-people' => 'Smileys & People',
        'animals-nature' => 'Animals & Nature',
        'food-drink' => 'Food & Drink',
        'travel-places' => 'Travel & Places',
        'activities' => 'Activities',
        'objects' => 'Objects',
        'symbols' => 'Symbols',
        'flags' => 'Flags',
    ],

    'custom' => [
        'nav' => 'Emojis',
        'tab' => 'Custom',
    ],

    'options' => [
        'default' => 'Default (:value)',
        'show' => 'Show',
        'hide' => 'Hide',
    ],

    'settings' => [
        'tab_general' => 'General',
        'section_set' => 'Emoji Set',
        'emoji_set_instructions' => 'How emojis look in the picker and on your site. OpenMoji requires attribution.',
        'emoji_set_free' => 'Native in the free edition. Noto, Fluent and OpenMoji images are part of Pro.',
        'section_picker' => 'Picker Defaults',
        'section_picker_instructions' => 'Fields can override these.',
        'suggestions' => 'Shortcode Suggestions',
        'suggestions_instructions' => 'Suggest emojis while typing :name in Emoji Text and Bard.',
        'recent_emojis' => 'Recently Used',
        'recent_emojis_instructions' => 'Show recently picked emojis in the search tab.',
        'tab_custom' => 'Custom Emojis',
        'custom_grid' => 'Uploaded Emojis',
        'custom_empty' => 'No custom emojis yet.',
        'custom_setup' => 'Run :command once to create the asset container for custom emojis.',
        'custom_manage' => 'Upload & manage',
        'tab_cache' => 'Image Cache',
        'cache' => 'Image Cache',
        'cache_instructions' => 'Images are stored on your server. Fill the cache in advance, or clear it after changing a set.',
        'cache_cached' => ':cached of :total cached',
        'cache_size' => ':size on disk',
        'cache_warm' => 'Fill cache',
        'cache_clear' => 'Clear',
        'cache_warming' => 'Fetching… :percent%',
        'cache_failed' => ':count not in this set (native fallback)',
        'pro_title' => 'Statamic Emojis Pro',
        'custom_pro' => 'Upload your own emoji images and use them as :shortcode: in the picker, Bard and your templates.',
        'cache_pro' => 'Show emojis as Noto, Fluent or OpenMoji images, served from your own domain, in the Control Panel and on your site.',
    ],

    // Fields of the custom emoji asset blueprint (stored as translation keys, translated in the CP)
    'blueprint' => [
        'name' => 'Name',
        'name_instructions' => 'Shown in the picker and used as the image\'s alt text.',
        'shortcode' => 'Shortcode',
        'shortcode_instructions' => 'Used as :shortcode: in content. Lowercase letters, numbers, - _ and +. Leave empty to use the filename. Renaming breaks existing references.',
        'keywords' => 'Keywords',
        'keywords_instructions' => 'Extra search terms for the picker.',
    ],

    'sets' => [
        'default' => 'Default (:set)',
        'native' => 'Native',
        'noto' => 'Noto',
        'fluent' => 'Fluent',
        'openmoji' => 'OpenMoji',
    ],

    'skin_tones' => [
        'none' => 'Default',
        'light' => 'Light',
        'medium-light' => 'Medium-light',
        'medium' => 'Medium',
        'medium-dark' => 'Medium-dark',
        'dark' => 'Dark',
    ],

    'config' => [
        'section' => 'Emoji Picker',
        'emoji_set' => 'Emoji Set',
        'custom_emojis' => 'Custom Emojis',
        'categories' => 'Categories',
        'categories_instructions' => 'Leave empty for the site-wide setting.',
        'skin_tone' => 'Skin Tone',
        'skin_tone_selector' => 'Skin Tone Selector',
    ],
];
