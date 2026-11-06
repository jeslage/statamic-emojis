# Statamic Emojis

A Statamic 6 addon that provides:

- **Emoji fieldtype**: extends the core Text fieldtype and adds an emoji picker.
- **Bard emoji button**: a Bard toolbar button that inserts emojis at the cursor.

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

- **Fieldtype**: add a field with `type: emoji` to a blueprint.
- **Bard**: in a Bard field's config, enable the `emoji` button.

## Tests

```bash
vendor/bin/phpunit
```
