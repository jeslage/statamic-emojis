# Contributing

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

```bash
composer update jeslage/statamic-emojis   # in the site
composer install && npm install           # in this addon (composer first: @statamic/cms comes from vendor)
npm run dev                               # HMR
npm run build                             # production build -> resources/dist
```

## Tests

```bash
vendor/bin/phpunit   # PHP
npm test             # JS (Vitest + jsdom)
```

The JS tests stub Statamic's UI components (`tests/js/stubs`). CI runs both.

## Documentation

These docs live in `docs/` and are built with [VitePress](https://vitepress.dev):

```bash
npm run docs:dev     # or: cd docs && npm install && npm run docs:dev
```

Every push to `main` that changes `docs/` deploys them to GitHub Pages.

## Releasing

Commits follow [Conventional Commits](https://www.conventionalcommits.org).
[release-please](https://github.com/googleapis/release-please) keeps a release PR with changelog and
version up to date; merging it creates the tag and release, and the workflow attaches `dist.tar.gz`.
The repository needs **"Allow GitHub Actions to create and approve pull requests"**.
