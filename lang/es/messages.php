<?php

return [
    'emoji' => 'Emoji',
    'insert_emoji' => 'Insertar emoji',
    'search' => 'Buscar…',
    'search_tab' => 'Buscar',
    'recent_tab' => 'Usados recientemente',
    'loading' => 'Cargando…',
    'no_results' => 'No se encontró ningún emoji.',
    'pick_an_emoji' => 'Elige un emoji…',
    'select_emoji' => 'Seleccionar emoji',
    'change_emoji' => 'Cambiar emoji',
    'remove_emoji' => 'Quitar emoji',
    'change_skin_tone' => 'Cambiar tono de piel',

    'fieldtypes' => [
        'emoji' => 'Emoji',
        'emoji_text' => 'Texto con emoji',
    ],

    'categories' => [
        'smileys-people' => 'Caras y personas',
        'animals-nature' => 'Animales y naturaleza',
        'food-drink' => 'Comida y bebida',
        'travel-places' => 'Viajes y lugares',
        'activities' => 'Actividades',
        'objects' => 'Objetos',
        'symbols' => 'Símbolos',
        'flags' => 'Banderas',
    ],

    'custom' => [
        'nav' => 'Emojis',
        'tab' => 'Personalizados',
    ],

    'options' => [
        'default' => 'Predeterminado (:value)',
        'show' => 'Mostrar',
        'hide' => 'Ocultar',
    ],

    'settings' => [
        'tab_general' => 'General',
        'section_set' => 'Conjunto de emojis',
        'emoji_set_instructions' => 'Cómo se ven los emojis en el selector y en tu sitio. OpenMoji requiere atribución.',
        'section_picker' => 'Valores predeterminados del selector',
        'section_picker_instructions' => 'Los campos pueden sobrescribirlos.',
        'suggestions' => 'Sugerencias de códigos cortos',
        'suggestions_instructions' => 'Sugiere emojis al escribir :nombre en Texto con emoji y Bard.',
        'recent_emojis' => 'Usados recientemente',
        'recent_emojis_instructions' => 'Muestra los emojis usados recientemente en la pestaña de búsqueda.',
        'tab_custom' => 'Emojis personalizados',
        'custom_grid' => 'Emojis subidos',
        'custom_empty' => 'Aún no hay emojis personalizados.',
        'custom_setup' => 'Ejecuta :command una vez para crear el contenedor de assets de los emojis personalizados.',
        'custom_manage' => 'Subir y gestionar',
        'tab_cache' => 'Caché de imágenes',
        'cache' => 'Caché de imágenes',
        'cache_instructions' => 'Las imágenes se guardan en tu servidor. Llena la caché por adelantado o vacíala después de cambiar un conjunto.',
        'cache_cached' => ':cached de :total en caché',
        'cache_size' => ':size en disco',
        'cache_warm' => 'Llenar caché',
        'cache_clear' => 'Vaciar',
        'cache_warming' => 'Descargando… :percent %',
        'cache_failed' => ':count no están en este conjunto (se muestra el nativo)',
    ],

    // Fields of the custom emoji asset blueprint (stored as translation keys, translated in the CP)
    'blueprint' => [
        'name' => 'Nombre',
        'name_instructions' => 'Se muestra en el selector y se usa como texto alternativo de la imagen.',
        'shortcode' => 'Código corto',
        'shortcode_instructions' => 'Se usa como :shortcode: en el contenido. Letras minúsculas, números, - _ y +. Déjalo vacío para usar el nombre del archivo. Cambiarlo rompe las referencias existentes.',
        'keywords' => 'Palabras clave',
        'keywords_instructions' => 'Términos de búsqueda adicionales para el selector.',
    ],

    'sets' => [
        'default' => 'Predeterminado (:set)',
        'native' => 'Nativo',
        'noto' => 'Noto',
        'fluent' => 'Fluent',
        'openmoji' => 'OpenMoji',
    ],

    'skin_tones' => [
        'none' => 'Predeterminado',
        'light' => 'Claro',
        'medium-light' => 'Claro medio',
        'medium' => 'Medio',
        'medium-dark' => 'Oscuro medio',
        'dark' => 'Oscuro',
    ],

    'config' => [
        'section' => 'Selector de emojis',
        'emoji_set' => 'Conjunto de emojis',
        'custom_emojis' => 'Emojis personalizados',
        'categories' => 'Categorías',
        'categories_instructions' => 'Déjalo vacío para usar el ajuste del sitio.',
        'skin_tone' => 'Tono de piel',
        'skin_tone_selector' => 'Selector de tono de piel',
    ],
];
