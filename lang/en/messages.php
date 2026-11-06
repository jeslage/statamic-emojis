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
        'emoji_set_instructions' => 'How emojis look in the picker and in the output of the emoji_images modifier. Noto, Fluent and OpenMoji are images served from your own domain. OpenMoji requires an attribution on your site.',
        'section_picker' => 'Picker Defaults',
        'section_picker_instructions' => 'Used by all emoji fields and the Bard emoji button. Each field can override them in its "Emoji Picker" settings.',
        'categories_instructions' => 'Categories the picker offers. Leave empty to allow all.',
        'recent_emojis' => 'Recently Used',
        'recent_emojis_instructions' => 'Show the emojis each editor picked recently above the list in the search tab (stored in their browser). Not in pickers with a category limit.',
        'tab_custom' => 'Custom Emojis',
        'custom_grid' => 'Uploaded Emojis',
        'custom_empty' => 'No custom emojis yet.',
        'custom_setup' => 'Run :command once to create the asset container for custom emojis.',
        'custom_manage' => 'Upload & manage',
        'tab_cache' => 'Image Cache',
        'cache' => 'Image Cache',
        'cache_instructions' => 'Images of the emoji sets are fetched once and stored on your server. Fill the cache in advance so nobody waits for the first view, or clear it after changing a set in the config.',
        'cache_cached' => ':cached of :total cached',
        'cache_size' => ':size on disk',
        'cache_warm' => 'Fill cache',
        'cache_clear' => 'Clear',
        'cache_warming' => 'Fetching… :percent%',
        'cache_failed' => ':count not in this set (native fallback)',
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
        'emoji_set_instructions' => 'How emojis look in the picker and in the output of the emoji_images modifier. Leave empty to use the site-wide set.',
        'custom_emojis' => 'Custom Emojis',
        'custom_emojis_instructions' => 'Offer the custom emojis below in the picker and render them in the output.',
        'categories' => 'Categories',
        'categories_instructions' => 'Categories the picker offers. Leave empty to use the site-wide setting.',
        'skin_tone' => 'Skin Tone',
        'skin_tone_instructions' => 'Skin tone the picker starts with for emojis that support it.',
        'skin_tone_selector' => 'Skin Tone Selector',
        'skin_tone_selector_instructions' => 'Let editors change the skin tone in the picker. Their choice is remembered in the browser.',
    ],
];
