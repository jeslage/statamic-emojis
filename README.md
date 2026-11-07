# Statamic Emojis

A Statamic 6 addon that provides:

- **Emoji fieldtype** (`emoji`): a single emoji, chosen with the picker. Shows a "Select emoji" button, then the chosen emoji with a button to remove it.
- **Emoji Text fieldtype** (`emoji_text`): extends the core Text fieldtype and adds an emoji picker that inserts at the cursor.
- **Bard emoji button**: a Bard toolbar button that inserts emojis at the cursor.
- **Shortcode suggestions**: typing `:rock` in Emoji Text and Bard suggests matching emojis.
- **API support**: emoji values are objects with name and image in the REST API and GraphQL.

The picker is built on [vue-frimousse](https://vue-frimousse.robertshaw.id), with category tabs, search, skin tones and a preview footer. Emoji data (Emojibase) is served through the Control Panel (see [Emoji data](#emoji-data)), emojis can be shown as Noto, Fluent or OpenMoji images (see [Emoji sets](#emoji-sets)), and editors can upload their own (see [Custom emojis](#custom-emojis)).

## Translations

- **Picker UI, Emojis page and the custom emoji asset blueprint** (labels, instructions, button texts): `lang/{locale}/messages.php`, available in JS as `__('statamic-emojis::messages.<key>')`. Ships with `en`, `de`, `es` and `fr`; override them in a site via `lang/vendor/statamic-emojis/{locale}/messages.php`.
- **Emoji categories, labels and search keywords** come from [Emojibase](https://emojibase.dev) in the Control Panel user's language (`resources/js/locale.js` maps Laravel locales such as `de_CH` or `zh_TW` to Emojibase locales, falling back to `en`).

## Local development

Add the addon to a Statamic site as a path repository in the site's `composer.json`:

```json
"require": {
    "jeslage/statamic-emojis": "*"
},
"repositories": [
    { "type": "path", "url": "../statamic-emojis" }
]
```

Then run the following:

```bash
composer update jeslage/statamic-emojis   # in the site
composer install && npm install          # in this addon
npm run dev                              # HMR while developing
npm run build                            # production build -> resources/dist
```

`npm install` needs `vendor/statamic/cms` because `@statamic/cms` is resolved from there, so run `composer install` first.

## Usage

- **Emoji** (`type: emoji`): stores a single emoji string, or `null` when removed.
- **Emoji Text** (`type: emoji_text`): accepts the Text field settings (placeholder, character limit,
  prepend, …) except input type and autocomplete: it is always a text input, which shows custom
  emojis as images and has the picker button inside.

As columns in entry listings (fields marked "listable"), both show their emojis like the field:
custom emojis as their image, Unicode emojis in the site-wide set.

## Emojis page

**Settings → Emojis** in the Control Panel (users with the "edit Statamic Emojis settings" permission;
the same settings are under Tools → Addons). Saved to `resources/addons/statamic-emojis.yaml`.

- **General:** the site-wide emoji set, and picker defaults (categories, skin tone, skin tone selector)
  plus **Recently used**: each editor's recently picked emojis as a row above the list in the search
  tab (up to two rows, hidden while searching; stored in their browser; only in pickers without a
  category limit).
- **Custom Emojis:** turn custom emojis on or off, see all uploaded ones, and open the asset
  container to upload more.
- **Image Cache:** cached images and disk size per emoji set, with buttons to fill (with progress) or
  clear a set's cache.

**Which value applies:** a field's own setting (except the emoji set, which is site-wide only), else the
Emojis page, else `config/statamic-emojis.php`
(and its `.env` variables). Values saved on the page take precedence over the config file.

## Field settings

Both fieldtypes share these picker settings. Empty values use the site-wide settings from the Emojis
page; the field shows them as "Default (…)":

| Setting              | Default | Description                                                                 |
| -------------------- | ------- | --------------------------------------------------------------------------- |
| `categories`         | all     | Category keys the picker offers, e.g. `[smileys-people, animals-nature]`   |
| `skin_tone`          | `none`  | Starting skin tone: `none`, `light`, `medium-light`, `medium`, `medium-dark`, `dark` |
| `skin_tone_selector` | `true`  | Show a skin tone button in the picker; the editor's choice is remembered per browser |
| `custom_emojis`      | `true`  | Offer the uploaded custom emojis (see [Custom emojis](#custom-emojis)) |

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

Set it on the Emojis page, or with `STATAMIC_EMOJIS_SET=noto` (`set` in the published config).
Default: `native`. The set is site-wide, so all fields and the front end look the same.

The set is used in the picker, the Emoji, Emoji Text and Bard fields in the Control Panel, and by the
`emojis` modifier. Image sets are SVGs that the
server fetches once (pinned versions, see the config), caches in `storage/statamic-emojis/sets` and
serves from `/!/statamic-emojis/sets/{set}/{codepoints}.svg`, so visitors' browsers never contact a
CDN. Emojis a set doesn't have fall back to the native character.

Fill the cache in advance, e.g. after deploying (otherwise images are fetched on first view). The
Image Cache tab on the Emojis page does the same with a progress bar:

```bash
php please emojis:warm noto   # or: fluent, openmoji; default: the site-wide set
```

### Adding, changing and disabling sets

The built-in sets are always available. In the published config (`php artisan vendor:publish
--tag=statamic-emojis-config`), `sets` only lists **additions or changes**, so you never have to
copy the built-in ones:

```php
// config/statamic-emojis.php
'sets' => [
    // A new set (names: lowercase letters only)
    'twemoji' => [
        'label' => 'Twemoji',
        'url' => 'https://cdn.jsdelivr.net/gh/jdecked/twemoji@15.1.0/assets/svg',
        'naming' => 'fluent',
    ],
    // Only the keys you set change, e.g. a self-hosted copy of a built-in set
    'noto' => ['url' => 'https://emojis.example.com/noto'],
],

// Remove sets from the selection: ['fluent'], or ['*'] for native emojis only
'disabled_sets' => [],
```

`naming` is how the set names its files (variants with and without the variation selector FE0F are
tried):

| `naming`   | File for 🧑‍🚀                          |
| ---------- | ------------------------------------- |
| `fluent`   | `1f9d1-200d-1f680.svg` (default)      |
| `openmoji` | `1F9D1-200D-1F680.svg`                |
| `noto`     | `2D/svg/emoji_u1f9d1_200d_1f680.svg`  |
| a class    | implementing `StatamicEmojis\Contracts\SetNaming` (`files(array $codepoints): array`) |

Sets can also be added in code, e.g. from a site's or another addon's service provider:

```php
use StatamicEmojis\EmojiSets;

EmojiSets::extend('twemoji', [
    'label' => 'Twemoji',
    'url' => 'https://cdn.jsdelivr.net/gh/jdecked/twemoji@15.1.0/assets/svg',
]);
```

`label` can be plain text or a translation key. Check the license of a set you add (Twemoji, for
example, is CC-BY 4.0 and needs an attribution).

Empty the cache, e.g. after changing a set's `url` in the config (images are fetched again on next
use; browsers may keep images they already loaded for up to 30 days):

```bash
php please emojis:clear noto     # one set; without an argument: all image sets
php please emojis:clear --data   # also forget the cached Emojibase data (names, categories, search)
```

### Output

In templates, values print as plain text (`{{ emoji }}` → 🎉). To render them in the set, use the
`emojis` modifier. It works on a single emoji, text and HTML (only text between tags is
replaced, attributes stay untouched):

```antlers
{{ emoji | emojis }}                 {{# site-wide set #}}
{{ content | emojis }}               {{# Bard / Emoji Text #}}
{{ content | emojis('openmoji') }}   {{# explicit set #}}
```

```blade
{!! Statamic::modify($page->emoji)->emojis() !!}
{!! Statamic::modify($page->content)->emojis('noto') !!}
<s:emojis :value="$page->content" />   {{-- the same with the emojis tag, see below --}}
```

The Emoji field also gives you the emoji's name and image directly:

```antlers
{{ emoji }}          {{# 🎉 #}}
{{ emoji:label }}    {{# party popper (Emojibase name in the site's language, or the custom emoji's name) #}}
{{ emoji:url }}      {{# image in the site-wide set or custom emoji; empty for native #}}
```

### The `emojis` tag

The same in tag form, for any value or emoji, plus a loop over the custom emojis:

```antlers
{{ emojis :value="content" }}                   {{# emojis as images, site-wide set #}}
{{ emojis value="Hi 🎉" set="noto" }}           {{# explicit set #}}
{{ emojis:url value="🎉" }}                     {{# image URL (empty for native) #}}
{{ emojis:label value="🎉" }}                   {{# party popper #}}

{{ emojis:custom }}
    <img src="{{ url }}" alt="{{ label }}"> {{ emoji }} {{# :party-parrot: #}}
{{ /emojis:custom }}
```

In Blade, use Statamic's tag syntax:

```blade
<s:emojis :value="$page->content" />
<s:emojis:custom>{{ $label }}: {{ $emoji }}</s:emojis:custom>
```

The loop provides `shortcode`, `emoji` (`:shortcode:`), `label`, `url` and `keywords`. The tag is named
`emojis` so it doesn't clash with fields named `emoji`.

Images get `class="emoji"`; size them with your CSS, e.g.
`img.emoji { width: 1.15em; height: 1.15em; vertical-align: -0.2em; }`.

### Limitations

- The picker hides emojis the *editor's* device can't render (e.g. flags in Chrome on Windows), even
  with an image set.

## Custom emojis

Editors can upload their own emoji images (PNG, GIF, WebP or SVG, up to 1 MB) and use them like
regular emojis, referenced by **shortcode**, e.g. `:party-parrot:`.

```bash
php please emojis:custom-setup   # once: creates the "statamic-emojis" asset container and its blueprint
```

- **Upload:** Settings → Emojis → Custom Emojis → "Upload & manage" (the "Emojis" asset container; Statamic's per-container
  permissions apply). Each image has a **Name** (picker label and alt text), an optional **Shortcode**
  (defaults to the filename, e.g. `party-parrot.png` → `:party-parrot:`) and **Keywords** for search.
- **Picker:** a "Custom" tab, and matching custom emojis above the regular search results. Fields can
  turn them off with the "Custom Emojis" setting.
- **Values:** the Emoji field stores `:shortcode:`, Emoji Text stores `:shortcode:` as text (shown as
  the image in the editor), and Bard inserts an inline image node (shown as the image in the editor).
- **Output:** Bard renders custom emojis as `<img class="emoji emoji-custom">` automatically. For Emoji
  and Emoji Text values, use the `emojis` modifier (in every emoji set, including `native`).
  Unknown shortcodes stay text.

Shortcodes are lowercase letters, numbers, `-`, `_` and `+`. **Renaming a shortcode breaks existing
references** (they show as `:old-name:` text). Folders are only for organizing: the shortcode comes from the
filename (or the Shortcode field), so `halloween/ghost.svg` is `:ghost:`. If two images share a shortcode,
the first by path wins. Uploaded SVGs are sanitized by Statamic (`svg_sanitization_on_upload`).

Images are stored on the `statamic-emojis` disk, which the addon defines as `public/statamic-emojis` unless your
`config/filesystems.php` has a disk with that name (e.g. on S3). Container, disk and the feature itself
can be changed in the config (`custom`) or with `STATAMIC_EMOJIS_CUSTOM`,
`STATAMIC_EMOJIS_CUSTOM_CONTAINER` and `STATAMIC_EMOJIS_CUSTOM_DISK`.

## Shortcode suggestions

Typing `:` and at least two characters (e.g. `:rock`) in an Emoji Text field or in a Bard field
with the emoji button lists matching emojis with their shortcode: custom emojis first, then emojis
by their [Emojibase shortcode](https://emojibase.dev/docs/shortcodes) (e.g. `:thumbsup:`), name and
keywords. ↑/↓ choose, Enter or Tab insert, Escape closes. Turn it off with "Shortcode
Suggestions" on the Emojis page.

## API (REST & GraphQL)

Emoji fields are objects in the API (in templates they still print as plain text):

```json
"mood": {
    "value": "👍🏻",
    "label": "thumbs up: light skin tone",
    "url": "https://example.com/!/statamic-emojis/sets/openmoji/1f44d-1f3fb.svg",
    "set": "openmoji",
    "custom": false,
    "shortcode": null
}
```

`url` is the image in the site-wide set (`null` for native) or the custom emoji's image; custom
emojis have `"value": ":party-parrot:"`, `"custom": true` and their `shortcode`. Emoji Text fields
are `{ "value": "Done 👍", "html": "Done <img class=\"emoji\" …>", "set": "noto" }`.

GraphQL has matching types, `EmojiValue` and `EmojiTextValue`:

```graphql
{
    entry(id: "…") {
        ... on Entry_Pages_Page {
            mood { value label url set custom shortcode }
            motto { value html set }
        }
    }
}
```

## Databases

Values are plain Unicode. Statamic's database driver stores field data JSON-encoded with ASCII
escapes (`\ud83d\ude00`), so it's safe in any MySQL character set. If you copy values into your own
text columns, use `utf8mb4` (Laravel's default) – the older 3-byte `utf8` can't hold emojis.

## Installation

```bash
composer require jeslage/statamic-emojis
php please emojis:custom-setup   # optional: container for custom emojis
```

Releases ship the built Control Panel assets (`dist.tar.gz`), which
[`pixelfear/composer-dist-plugin`](https://github.com/pixelfear/composer-dist-plugin) downloads
into `resources/dist` on install. When installing a development branch, build them yourself
(`npm ci && npm run build`).

## Releasing

Commits follow [Conventional Commits](https://www.conventionalcommits.org) (`feat:`, `fix:`, …).
[release-please](https://github.com/googleapis/release-please) keeps a release pull request with the
changelog and next version up to date; merging it creates the tag and GitHub release, and the
release workflow attaches `dist.tar.gz`. Before 1.0, `feat` bumps the minor and `fix` the patch
version. The repository needs "Allow GitHub Actions to create and approve pull requests"
(Settings → Actions → General).

## Tests

```bash
vendor/bin/phpunit   # PHP: fieldtypes, sets, modifier, tag, API, settings
npm test             # JS (Vitest + jsdom): emoji matching, search, the Emoji Text editor
```

The JS tests replace Statamic's UI components with small stand-ins (`tests/js/stubs`), as the
Control Panel provides the real ones at runtime. CI runs both.
