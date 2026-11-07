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
        'emoji_set_instructions' => 'Cómo se ven los emojis en el selector y en la salida del modificador emoji_images. Noto, Fluent y OpenMoji son imágenes servidas desde tu propio dominio. OpenMoji requiere una atribución en tu sitio.',
        'section_picker' => 'Valores predeterminados del selector',
        'section_picker_instructions' => 'Se usan en todos los campos de emoji y en el botón de emoji de Bard. Cada campo puede sobrescribirlos en sus ajustes del selector de emojis.',
        'categories_instructions' => 'Categorías que ofrece el selector. Déjalo vacío para permitir todas.',
        'suggestions' => 'Sugerencias de códigos cortos',
        'suggestions_instructions' => 'Sugiere emojis al escribir :nombre, p. ej. :rock, en campos de texto con emoji y en campos Bard con el botón de emoji.',
        'recent_emojis' => 'Usados recientemente',
        'recent_emojis_instructions' => 'Muestra los emojis que cada editor eligió recientemente sobre la lista en la pestaña de búsqueda (guardados en su navegador). No en selectores con límite de categorías.',
        'tab_custom' => 'Emojis personalizados',
        'custom_grid' => 'Emojis subidos',
        'custom_empty' => 'Aún no hay emojis personalizados.',
        'custom_setup' => 'Ejecuta :command una vez para crear el contenedor de assets de los emojis personalizados.',
        'custom_manage' => 'Subir y gestionar',
        'tab_cache' => 'Caché de imágenes',
        'cache' => 'Caché de imágenes',
        'cache_instructions' => 'Las imágenes de los conjuntos de emojis se descargan una vez y se guardan en tu servidor. Llena la caché por adelantado para que nadie espere la primera vista, o vacíala después de cambiar un conjunto en la configuración.',
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
        'custom_emojis_instructions' => 'Ofrecer los emojis personalizados de abajo en el selector y mostrarlos en la salida.',
        'categories' => 'Categorías',
        'categories_instructions' => 'Categorías que ofrece el selector. Déjalo vacío para usar el ajuste de todo el sitio.',
        'skin_tone' => 'Tono de piel',
        'skin_tone_instructions' => 'Tono de piel con el que empieza el selector para los emojis que lo admiten.',
        'skin_tone_selector' => 'Selector de tono de piel',
        'skin_tone_selector_instructions' => 'Permite a los editores cambiar el tono de piel en el selector. Su elección se guarda en el navegador.',
    ],
];
