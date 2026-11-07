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
        'emoji_set_instructions' => 'Wie Emojis in der Auswahl und in der Ausgabe des Modifiers „emojis“ aussehen. Noto, Fluent und OpenMoji sind Bilder, die von deiner eigenen Domain ausgeliefert werden. OpenMoji verlangt eine Namensnennung auf deiner Website.',
        'section_picker' => 'Standards der Auswahl',
        'section_picker_instructions' => 'Gilt für alle Emoji-Felder und den Emoji-Button in Bard. Jedes Feld kann sie in seinen Einstellungen unter „Emoji-Auswahl“ überschreiben.',
        'categories_instructions' => 'Kategorien, die die Auswahl anbietet. Leer lassen, um alle zu erlauben.',
        'suggestions' => 'Shortcode-Vorschläge',
        'suggestions_instructions' => 'Schlägt beim Tippen von :name, z. B. :rock, Emojis vor – in Emoji-Text-Feldern und in Bard-Feldern mit Emoji-Button.',
        'recent_emojis' => 'Zuletzt verwendet',
        'recent_emojis_instructions' => 'Zeigt die zuletzt gewählten Emojis jedes Redakteurs über der Liste im Such-Tab (in seinem Browser gespeichert). Nicht in Auswahlen mit Kategorie-Einschränkung.',
        'tab_custom' => 'Eigene Emojis',
        'custom_grid' => 'Hochgeladene Emojis',
        'custom_empty' => 'Noch keine eigenen Emojis.',
        'custom_setup' => 'Führe einmal :command aus, um den Asset-Container für eigene Emojis anzulegen.',
        'custom_manage' => 'Hochladen & verwalten',
        'tab_cache' => 'Bild-Cache',
        'cache' => 'Bild-Cache',
        'cache_instructions' => 'Bilder der Emoji-Sets werden einmal abgerufen und auf deinem Server gespeichert. Fülle den Cache vorab, damit niemand auf die erste Anzeige wartet, oder leere ihn, nachdem du ein Set in der Konfiguration geändert hast.',
        'cache_cached' => ':cached von :total im Cache',
        'cache_size' => ':size auf der Festplatte',
        'cache_warm' => 'Cache füllen',
        'cache_clear' => 'Leeren',
        'cache_warming' => 'Wird abgerufen… :percent %',
        'cache_failed' => ':count nicht in diesem Set (native Darstellung)',
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
        'custom_emojis_instructions' => 'Die eigenen Emojis unten in der Auswahl anbieten und in der Ausgabe darstellen.',
        'categories' => 'Kategorien',
        'categories_instructions' => 'Kategorien, die die Auswahl anbietet. Leer lassen, um die seitenweite Einstellung zu verwenden.',
        'skin_tone' => 'Hautfarbe',
        'skin_tone_instructions' => 'Hautfarbe, mit der die Auswahl für Emojis startet, die sie unterstützen.',
        'skin_tone_selector' => 'Hautfarben-Auswahl',
        'skin_tone_selector_instructions' => 'Redakteure können die Hautfarbe in der Auswahl ändern. Ihre Wahl wird im Browser gespeichert.',
    ],
];
