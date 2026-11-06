# Statamic Emojis

A Statamic 6 addon that provides:

- **Emoji fieldtype**: extends the core Text fieldtype and adds an emoji picker.
- **Bard emoji button**: a Bard toolbar button that inserts emojis at the cursor.

The picker is built on [vue-frimousse](https://vue-frimousse.robertshaw.id). Emoji data (Emojibase) is loaded from jsDelivr at runtime by default.

## Translations

- **Picker UI** (search placeholder, loading/empty states, button labels): `lang/{locale}/messages.php`, available in JS as `__('statamic-emojis::messages.<key>')`. Ships with `en` and `de`; override them in a site via `lang/vendor/statamic-emojis/{locale}/messages.php`.
- **Emoji categories, labels and search keywords** come from [Emojibase](https://emojibase.dev) in the Control Panel user's language (`resources/js/locale.js` maps Laravel locales such as `de_CH` or `zh_TW` to Emojibase locales, falling back to `en`).

## Local development

Add the addon to a Statamic site as a path repository in the site's `composer.json`:

```json
"require": {
    "convoy/statamic-emojis": "*"
},
"repositories": [
    { "type": "path", "url": "../statamic-emojis" }
]
```

Then run the following:

```bash
composer update convoy/statamic-emojis   # in the site
composer install && npm install          # in this addon
npm run dev                              # HMR while developing
npm run build                            # production build -> resources/dist
```

`npm install` needs `vendor/statamic/cms` because `@statamic/cms` is resolved from there, so run `composer install` first.

## Usage

- **Fieldtype**: add a field with `type: emoji` to a blueprint. It accepts all Text field settings, plus:

  | Setting              | Default | Description                                                                 |
  | -------------------- | ------- | --------------------------------------------------------------------------- |
  | `categories`         | all     | Category keys the picker offers, e.g. `[smileys-emotion, animals-nature]`   |
  | `skin_tone`          | `none`  | Starting skin tone: `none`, `light`, `medium-light`, `medium`, `medium-dark`, `dark` |
  | `skin_tone_selector` | `true`  | Show a skin tone button in the picker; the editor's choice is remembered per browser |

  Category keys: `smileys-emotion`, `people-body`, `animals-nature`, `food-drink`, `travel-places`, `activities`, `objects`, `symbols`, `flags`.

- **Bard**: in a Bard field's config, enable the `emoji` button (all categories, default skin tone).

## Emoji data

The picker loads Emojibase data through the Control Panel route
`/cp/statamic-emojis/emojibase/{categories}/{locale}/{data|messages}.json`. The server fetches it once
from jsDelivr, caches it, and filters it by the field's categories, so browsers never talk to the CDN.

Publish the config to change the source (e.g. a self-hosted copy of `emojibase-data`) or the cache time:

```bash
php artisan vendor:publish --tag=statamic-emojis-config
```

```env
STATAMIC_EMOJIS_EMOJIBASE_URL=https://cdn.jsdelivr.net/npm/emojibase-data@18
STATAMIC_EMOJIS_CACHE_TTL=604800
```

## Tests

```bash
vendor/bin/phpunit
```
