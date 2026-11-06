# Statamic Emojis

A Statamic 6 addon that provides:

- **Emoji fieldtype** (`emoji`): a single emoji, chosen with the picker. Shows a "Select emoji" button, then the chosen emoji with a button to remove it.
- **Emoji Text fieldtype** (`emoji_text`): extends the core Text fieldtype and adds an emoji picker that inserts at the cursor.
- **Bard emoji button**: a Bard toolbar button that inserts emojis at the cursor.

The picker is built on [vue-frimousse](https://vue-frimousse.robertshaw.id), with category tabs, search, skin tones and a preview footer. Emoji data (Emojibase) is served through the Control Panel (see [Emoji data](#emoji-data)), and emojis can be shown as Noto, Fluent or OpenMoji images (see [Emoji sets](#emoji-sets)).

## Translations

- **Picker UI** (search placeholder, loading/empty states, button labels): `lang/{locale}/messages.php`, available in JS as `__('statamic-emojis::messages.<key>')`. Ships with `en`, `de`, `es` and `fr`; override them in a site via `lang/vendor/statamic-emojis/{locale}/messages.php`.
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

- **Emoji** (`type: emoji`): stores a single emoji string, or `null` when removed.
- **Emoji Text** (`type: emoji_text`): accepts all Text field settings (placeholder, character limit, prepend, …).

Both fieldtypes share these picker settings:

| Setting              | Default | Description                                                                 |
| -------------------- | ------- | --------------------------------------------------------------------------- |
| `categories`         | all     | Category keys the picker offers, e.g. `[smileys-people, animals-nature]`   |
| `skin_tone`          | `none`  | Starting skin tone: `none`, `light`, `medium-light`, `medium`, `medium-dark`, `dark` |
| `skin_tone_selector` | `true`  | Show a skin tone button in the picker; the editor's choice is remembered per browser |
| `emoji_set`          | site-wide | `native`, `noto`, `fluent` or `openmoji` (see [Emoji sets](#emoji-sets)) |

Category keys: `smileys-people` (smileys, emotion, people and body, merged like Slack), `animals-nature`, `food-drink`, `travel-places`, `activities`, `objects`, `symbols`, `flags`.

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

## Emoji sets

Emojis are always **stored as Unicode**. How they look is a setting, so it can change any time:

| Set        | Looks like             | License                                                        |
| ---------- | ---------------------- | -------------------------------------------------------------- |
| `native`   | The device's own font  | –                                                              |
| `noto`     | Google / Android       | Apache 2.0 (flags: public domain)                              |
| `fluent`   | Microsoft Fluent (flat) | MIT                                                           |
| `openmoji` | OpenMoji               | CC BY-SA 4.0, **attribution required**: "Emoji graphics by OpenMoji, https://openmoji.org" |

Apple's emoji artwork is proprietary and can't be used as an image set; visitors on Apple devices
see it with `native`.

- **Site-wide:** `STATAMIC_EMOJIS_SET=noto` (or `set` in the published config). Default: `native`.
- **Per field:** the "Emoji Set" setting in the field's "Emoji Picker" section overrides it.

The set is used in the picker and by the `emoji_images` modifier. Image sets are SVGs that the
server fetches once (pinned versions, see the config), caches in `storage/statamic-emojis/sets` and
serves from `/!/statamic-emojis/sets/{set}/{codepoints}.svg`, so visitors' browsers never contact a
CDN. Emojis a set doesn't have fall back to the native character.

Fill the cache in advance, e.g. after deploying (otherwise images are fetched on first view):

```bash
php please emojis:warm noto   # or: fluent, openmoji; default: the site-wide set
```

Empty the cache, e.g. after changing a set's `url` in the config (images are fetched again on next
use; browsers may keep images they already loaded for up to 30 days):

```bash
php please emojis:clear noto     # one set; without an argument: all image sets
php please emojis:clear --data   # also forget the cached Emojibase data (names, categories, search)
```

### Output

Values stay plain strings. To render them in the set, use the `emoji_images` modifier. It works on
a single emoji, text and HTML (only text between tags is replaced, attributes stay untouched):

```antlers
{{ emoji | emoji_images }}                 {{# field's set, else the site-wide set #}}
{{ content | emoji_images }}               {{# Bard / Emoji Text #}}
{{ content | emoji_images('openmoji') }}   {{# explicit set #}}
```

```blade
{!! Statamic::modify($page->augmentedValue('emoji'))->emoji_images() !!}
{!! Statamic::modify($page->content)->emoji_images('noto') !!}
```

In Blade, pass the `Value` (`$entry->augmentedValue('handle')`, or
`$values->getProxiedInstance()->get('handle')` in grids and replicators) so the field's set is used;
a plain string uses the site-wide set. Antlers finds the field automatically. If fields with
different sets hold the same value, pass the set explicitly.

Images get `class="emoji"`; size them with your CSS, e.g.
`img.emoji { width: 1.15em; height: 1.15em; vertical-align: -0.2em; }`.

### Limitations

- The Emoji Text input and the Bard editor show native characters in the Control Panel; images
  appear in the picker, the Emoji field and the front-end output.
- The picker hides emojis the *editor's* device can't render (e.g. flags in Chrome on Windows), even
  with an image set.

## Databases

Values are plain Unicode. Statamic's database driver stores field data JSON-encoded with ASCII
escapes (`\ud83d\ude00`), so it's safe in any MySQL character set. If you copy values into your own
text columns, use `utf8mb4` (Laravel's default) – the older 3-byte `utf8` can't hold emojis.

## Tests

```bash
vendor/bin/phpunit
```
