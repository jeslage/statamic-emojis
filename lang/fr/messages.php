<?php

return [
    'emoji' => 'Emoji',
    'insert_emoji' => 'Insérer un emoji',
    'search' => 'Rechercher…',
    'search_tab' => 'Recherche',
    'recent_tab' => 'Utilisés récemment',
    'loading' => 'Chargement…',
    'no_results' => 'Aucun emoji trouvé.',
    'pick_an_emoji' => 'Choisissez un emoji…',
    'select_emoji' => 'Choisir un emoji',
    'change_emoji' => 'Changer l’emoji',
    'remove_emoji' => 'Supprimer l’emoji',
    'change_skin_tone' => 'Changer la couleur de peau',

    'fieldtypes' => [
        'emoji' => 'Emoji',
        'emoji_text' => 'Texte avec emoji',
    ],

    'categories' => [
        'smileys-people' => 'Smileys et personnes',
        'animals-nature' => 'Animaux et nature',
        'food-drink' => 'Nourriture et boissons',
        'travel-places' => 'Voyages et lieux',
        'activities' => 'Activités',
        'objects' => 'Objets',
        'symbols' => 'Symboles',
        'flags' => 'Drapeaux',
    ],

    'custom' => [
        'nav' => 'Emojis',
        'tab' => 'Personnalisés',
    ],

    'options' => [
        'default' => 'Par défaut (:value)',
        'show' => 'Afficher',
        'hide' => 'Masquer',
    ],

    'settings' => [
        'tab_general' => 'Général',
        'section_set' => 'Jeu d’emojis',
        'emoji_set_instructions' => 'Apparence des emojis dans le sélecteur et dans la sortie du modificateur emoji_images. Noto, Fluent et OpenMoji sont des images servies depuis votre propre domaine. OpenMoji exige une attribution sur votre site.',
        'section_picker' => 'Valeurs par défaut du sélecteur',
        'section_picker_instructions' => 'Utilisées par tous les champs emoji et le bouton emoji de Bard. Chaque champ peut les remplacer dans ses réglages du sélecteur d’emojis.',
        'categories_instructions' => 'Catégories proposées par le sélecteur. Laisser vide pour toutes les autoriser.',
        'suggestions' => 'Suggestions de codes courts',
        'suggestions_instructions' => 'Propose des emojis en tapant :nom, par ex. :rock, dans les champs Texte avec emoji et dans les champs Bard avec le bouton emoji.',
        'recent_emojis' => 'Utilisés récemment',
        'recent_emojis_instructions' => 'Affiche les emojis choisis récemment par chaque éditeur au-dessus de la liste dans l’onglet de recherche (enregistrés dans son navigateur). Pas dans les sélecteurs limités à certaines catégories.',
        'tab_custom' => 'Emojis personnalisés',
        'custom_grid' => 'Emojis importés',
        'custom_empty' => 'Aucun emoji personnalisé pour le moment.',
        'custom_setup' => 'Exécutez :command une fois pour créer le conteneur d’assets des emojis personnalisés.',
        'custom_manage' => 'Importer et gérer',
        'tab_cache' => 'Cache d’images',
        'cache' => 'Cache d’images',
        'cache_instructions' => 'Les images des jeux d’emojis sont téléchargées une fois et stockées sur votre serveur. Remplissez le cache à l’avance pour que personne n’attende le premier affichage, ou videz-le après avoir modifié un jeu dans la configuration.',
        'cache_cached' => ':cached sur :total en cache',
        'cache_size' => ':size sur le disque',
        'cache_warm' => 'Remplir le cache',
        'cache_clear' => 'Vider',
        'cache_warming' => 'Téléchargement… :percent %',
        'cache_failed' => ':count absents de ce jeu (affichage natif)',
    ],

    // Fields of the custom emoji asset blueprint (stored as translation keys, translated in the CP)
    'blueprint' => [
        'name' => 'Nom',
        'name_instructions' => 'Affiché dans le sélecteur et utilisé comme texte alternatif de l’image.',
        'shortcode' => 'Code court',
        'shortcode_instructions' => 'Utilisé sous la forme :shortcode: dans le contenu. Lettres minuscules, chiffres, - _ et +. Laisser vide pour utiliser le nom du fichier. Le renommer casse les références existantes.',
        'keywords' => 'Mots-clés',
        'keywords_instructions' => 'Termes de recherche supplémentaires pour le sélecteur.',
    ],

    'sets' => [
        'default' => 'Par défaut (:set)',
        'native' => 'Natif',
        'noto' => 'Noto',
        'fluent' => 'Fluent',
        'openmoji' => 'OpenMoji',
    ],

    'skin_tones' => [
        'none' => 'Par défaut',
        'light' => 'Claire',
        'medium-light' => 'Moyennement claire',
        'medium' => 'Moyenne',
        'medium-dark' => 'Moyennement foncée',
        'dark' => 'Foncée',
    ],

    'config' => [
        'section' => 'Sélecteur d’emojis',
        'emoji_set' => 'Jeu d’emojis',
        'emoji_set_instructions' => 'Apparence des emojis dans le sélecteur et dans la sortie du modificateur emoji_images. Laisser vide pour utiliser le jeu du site.',
        'custom_emojis' => 'Emojis personnalisés',
        'custom_emojis_instructions' => 'Proposer les emojis personnalisés ci-dessous dans le sélecteur et les afficher dans la sortie.',
        'categories' => 'Catégories',
        'categories_instructions' => 'Catégories proposées par le sélecteur. Laisser vide pour utiliser le réglage du site.',
        'skin_tone' => 'Couleur de peau',
        'skin_tone_instructions' => 'Couleur de peau initiale du sélecteur pour les emojis qui la prennent en charge.',
        'skin_tone_selector' => 'Sélecteur de couleur de peau',
        'skin_tone_selector_instructions' => 'Permet aux éditeurs de changer la couleur de peau dans le sélecteur. Leur choix est mémorisé dans le navigateur.',
    ],
];
