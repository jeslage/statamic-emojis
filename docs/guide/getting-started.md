# Editions & installation

## Editions

| Feature                                                          | Free | Pro |
| ---------------------------------------------------------------- | :--: | :-: |
| Emoji and Emoji Text fieldtypes, Bard button                     |  ✅  | ✅  |
| Shortcode suggestions, recently used, skin tones                 |  ✅  | ✅  |
| `emojis` modifier and tag, REST & GraphQL                        |  ✅  | ✅  |
| Native emojis                                                    |  ✅  | ✅  |
| [Emoji sets](./emoji-sets): Noto, Fluent, OpenMoji, your own     |  –   | ✅  |
| [Custom emojis](./custom-emojis)                                 |  –   | ✅  |

The free edition is the default. **Pro needs a license from the
[Statamic Marketplace](https://statamic.com/addons)** for each site in production; enable it in
`config/statamic/editions.php`:

```php
'addons' => [
    'jeslage/statamic-emojis' => 'pro',
],
```

## Installation

```bash
composer require jeslage/statamic-emojis
php please emojis:custom-setup   # Pro, optional: asset container for custom emojis
```

Releases ship the built Control Panel assets, which
[`pixelfear/composer-dist-plugin`](https://github.com/pixelfear/composer-dist-plugin) downloads into
`resources/dist`. **For a development branch, build them yourself** (`npm ci && npm run build`).

Next: add an [emoji field](./fieldtypes) to a blueprint, or pick the site-wide defaults under
[Settings → Emojis](./settings).
