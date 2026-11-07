<?php

return [
    'emoji' => 'Emoji',
    'insert_emoji' => 'Emoji einfügen',
    'search' => 'Suchen…',
    'search_tab' => 'Suche',
    'recent_tab' => 'Zuletzt verwendet',
    'loading' => 'Wird geladen…',
    'no_results' => 'Kein Emoji gefunden.',
    'pick_an_emoji' => 'Emoji auswählen…',
    'select_emoji' => 'Emoji auswählen',
    'change_emoji' => 'Emoji ändern',
    'remove_emoji' => 'Emoji entfernen',
    'change_skin_tone' => 'Hautfarbe ändern',

    'fieldtypes' => [
        'emoji' => 'Emoji',
        'emoji_text' => 'Emoji-Text',
    ],

    'categories' => [
        'smileys-people' => 'Smileys & Menschen',
        'animals-nature' => 'Tiere & Natur',
        'food-drink' => 'Essen & Trinken',
        'travel-places' => 'Reisen & Orte',
        'activities' => 'Aktivitäten',
        'objects' => 'Objekte',
        'symbols' => 'Symbole',
        'flags' => 'Flaggen',
    ],

    'custom' => [
        'nav' => 'Emojis',
        'tab' => 'Eigene',
    ],

    'options' => [
        'default' => 'Standard (:value)',
        'show' => 'Anzeigen',
        'hide' => 'Ausblenden',
    ],

    'settings' => [
        'tab_general' => 'Allgemein',
        'section_set' => 'Emoji-Set',
        'emoji_set_instructions' => 'Wie Emojis in der Auswahl und auf deiner Website aussehen. OpenMoji verlangt eine Namensnennung.',
        'emoji_set_free' => 'In der kostenlosen Edition nativ. Noto-, Fluent- und OpenMoji-Bilder sind Teil von Pro.',
        'section_picker' => 'Standards der Auswahl',
        'section_picker_instructions' => 'Felder können sie überschreiben.',
        'suggestions' => 'Shortcode-Vorschläge',
        'suggestions_instructions' => 'Schlägt beim Tippen von :name Emojis vor, in Emoji-Text und Bard.',
        'recent_emojis' => 'Zuletzt verwendet',
        'recent_emojis_instructions' => 'Zeigt zuletzt gewählte Emojis im Such-Tab.',
        'tab_custom' => 'Eigene Emojis',
        'custom_grid' => 'Hochgeladene Emojis',
        'custom_empty' => 'Noch keine eigenen Emojis.',
        'custom_setup' => 'Führe einmal :command aus, um den Asset-Container für eigene Emojis anzulegen.',
        'custom_manage' => 'Hochladen & verwalten',
        'tab_cache' => 'Bild-Cache',
        'cache' => 'Bild-Cache',
        'cache_instructions' => 'Bilder werden auf deinem Server gespeichert. Fülle den Cache vorab oder leere ihn, nachdem du ein Set geändert hast.',
        'cache_cached' => ':cached von :total im Cache',
        'cache_size' => ':size auf der Festplatte',
        'cache_warm' => 'Cache füllen',
        'cache_clear' => 'Leeren',
        'cache_warming' => 'Wird abgerufen… :percent %',
        'cache_failed' => ':count nicht in diesem Set (native Darstellung)',
        'pro_title' => 'Statamic Emojis Pro',
        'custom_pro' => 'Lade eigene Emoji-Bilder hoch und verwende sie als :shortcode: in der Auswahl, in Bard und in deinen Templates.',
        'cache_pro' => 'Zeige Emojis als Noto-, Fluent- oder OpenMoji-Bilder von deiner eigenen Domain, im Control Panel und auf deiner Website.',
    ],

    // Fields of the custom emoji asset blueprint (stored as translation keys, translated in the CP)
    'blueprint' => [
        'name' => 'Name',
        'name_instructions' => 'Wird in der Auswahl angezeigt und als Alternativtext des Bildes verwendet.',
        'shortcode' => 'Shortcode',
        'shortcode_instructions' => 'Wird im Inhalt als :shortcode: verwendet. Kleinbuchstaben, Zahlen, - _ und +. Leer lassen, um den Dateinamen zu verwenden. Umbenennen bricht bestehende Verweise.',
        'keywords' => 'Suchbegriffe',
        'keywords_instructions' => 'Zusätzliche Suchbegriffe für die Auswahl.',
    ],

    'sets' => [
        'default' => 'Standard (:set)',
        'native' => 'Nativ',
        'noto' => 'Noto',
        'fluent' => 'Fluent',
        'openmoji' => 'OpenMoji',
    ],

    'skin_tones' => [
        'none' => 'Standard',
        'light' => 'Hell',
        'medium-light' => 'Mittelhell',
        'medium' => 'Mittel',
        'medium-dark' => 'Mitteldunkel',
        'dark' => 'Dunkel',
    ],

    'config' => [
        'section' => 'Emoji-Auswahl',
        'emoji_set' => 'Emoji-Set',
        'custom_emojis' => 'Eigene Emojis',
        'categories' => 'Kategorien',
        'categories_instructions' => 'Leer lassen für die seitenweite Einstellung.',
        'skin_tone' => 'Hautfarbe',
        'skin_tone_selector' => 'Hautfarben-Auswahl',
    ],
];
