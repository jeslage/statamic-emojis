# Statamic playground

A basic local Statamic site for developing and testing the addon. Only this README,
`setup.sh` and `seed/` are committed; the install itself is gitignored.

## Fresh install

```bash
cd playground
./setup.sh               # asks: local PHP or DDEV
./setup.sh --local       # or pick directly
./setup.sh --ddev
./setup.sh --no-seed     # blank Statamic site without the test content
```

`setup.sh` creates a Statamic project here and links the addon (this folder's parent) via a
Composer path repository. Because it's a symlink, PHP changes apply immediately.

### Local PHP

You need `php` and `composer` on your machine.

```bash
php artisan serve        # http://127.0.0.1:8000/cp
```

### DDEV

You need [DDEV](https://ddev.com). The script creates and starts the DDEV project
`statamic-emojis`; the site runs at https://statamic-emojis.ddev.site (`ddev launch /cp`).

Prefix PHP and Composer commands with `ddev`, e.g. `ddev php please …`. The addon folder is
mounted into the container at `/var/<addon-folder>` (`.ddev/docker-compose.addon.yaml`), so
the relative path repository and the `vendor/` symlink also resolve inside the container.

```bash
ddev stop                        # stop the project
ddev delete -Oy statamic-emojis  # remove containers and database
```

## Addon assets (JS/CSS)

The Control Panel loads the addon's Vite assets. Run these from the addon root (`..`):

```bash
composer install && npm install   # once; @statamic/cms is resolved from vendor/
npm run dev                       # HMR: the CP picks up resources/dist/hot automatically
```

Or build and publish the assets once:

```bash
npm run build                                                # in the addon root
php artisan vendor:publish --tag=statamic-emojis --force     # in the playground
```

## Test content (`seed/`)

Unless you pass `--no-seed`, `setup.sh` copies `seed/` over the fresh install. The folder
mirrors a Statamic site root:

| File                                                | Contents                                                     |
| --------------------------------------------------- | ------------------------------------------------------------ |
| `content/collections/pages.yaml`                    | `pages` collection (structured, like the skeleton)           |
| `content/trees/collections/pages.yaml`              | Tree: `Home`, `Emoji Fields`, `Emoji Fields (Blade)`         |
| `resources/blueprints/collections/pages/pages.yaml` | Page blueprint with the test fields below                    |
| `content/collections/pages/*.md`                    | `home`, `emoji-fields` and `emoji-fields-blade` (same data)  |
| `resources/views/emoji-fields.antlers.html`         | Antlers page template (inside `layout.antlers.html`)         |
| `resources/views/partials/emoji-fields.antlers.html`| `{{ partial:emoji-fields }}`, outputs every test field       |
| `resources/views/emoji-fields-blade.blade.php`      | Blade page template (full document, no Antlers layout)       |
| `resources/views/components/emoji-fields.blade.php` | `<x-emoji-fields :page="$page" />`, outputs every test field |

Fields in the `pages` blueprint:

| Handle                  | Type       | Tests                                                     |
| ----------------------- | ---------- | --------------------------------------------------------- |
| `emoji`                 | emoji      | picker-only field, default config                         |
| `emoji_empty`           | emoji      | no value: "Select emoji" button                           |
| `emoji_configured`      | emoji      | OpenMoji set, only Smileys & People and Animals, skin tone `medium-dark` |
| `emoji_read_only`       | emoji      | read-only visibility (no buttons)                         |
| `emoji_required`        | emoji      | validation, skin tone selector hidden                     |
| `emoji_text`            | emoji_text | text field with picker, default config                    |
| `emoji_text_configured` | emoji_text | inherited Text config (placeholder, character limit, prepend), Fluent set, categories, skin tone |
| `emoji_text_required`   | emoji_text | validation, skin tone selector hidden                     |
| `content`               | bard       | `emoji` toolbar button enabled                            |
| `content_without_emoji` | bard       | control: button not configured, so it must not show       |
| `blocks`                | replicator | Emoji field and Bard with emoji button inside a set       |
| `reactions`             | grid       | Emoji field (Noto set) in grid rows                       |

Both templates show each value as stored and through the `emojis` modifier, plus a row per
emoji set. Run `php please emojis:warm noto` (and `fluent`, `openmoji`) once to fill the image cache.

Custom emojis: the seed ships the `statamic-emojis` asset container (title "Emojis") with three sample images
(`:sparkle-star:`, `:pixel-heart:`, `:approved:`), used in Emoji Text and Bard. Settings, the
custom emoji grid and the image cache are under Settings → Emojis.

The two emoji pages hold identical data, one rendered with Antlers (`/emoji-fields`) and one
with Blade (`/emoji-fields-blade`), so the addon's output can be compared in both engines.
Statamic only wraps Antlers templates in a layout, so the Blade page template is a full HTML
document.

Content you change in the Control Panel stays local. Copy files back into `seed/` if they
should be committed.

## Using an existing Statamic site instead

```bash
composer config repositories.statamic-emojis path ../path/to/statamic-emojis
composer require jeslage/statamic-emojis:@dev
```
