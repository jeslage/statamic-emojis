# Configuration

Most options can be set on the [Emojis page](../guide/settings) in the Control Panel. **A field's own
setting wins, then the Emojis page, then `config/statamic-emojis.php`.** Publish the config with:

```bash
php artisan vendor:publish --tag=statamic-emojis-config
```

## Environment variables

| Variable                           | Default                                         | Description                                                  |
| ---------------------------------- | ----------------------------------------------- | ------------------------------------------------------------ |
| `STATAMIC_EMOJIS_SET`              | `native`                                        | Site-wide [emoji set](../guide/emoji-sets) (Pro)             |
| `STATAMIC_EMOJIS_CUSTOM`           | `true`                                          | Enable [custom emojis](../guide/custom-emojis) (Pro)         |
| `STATAMIC_EMOJIS_CUSTOM_CONTAINER` | `statamic-emojis`                               | Asset container for custom emojis                            |
| `STATAMIC_EMOJIS_CUSTOM_DISK`      | `statamic-emojis`                               | Filesystem disk for custom emojis                            |
| `STATAMIC_EMOJIS_EMOJIBASE_URL`    | `https://cdn.jsdelivr.net/npm/emojibase-data@18` | Where the server fetches [emoji data](./emoji-data) from     |
| `STATAMIC_EMOJIS_CACHE_TTL`        | `604800` (7 days)                               | How long the emoji data is cached, in seconds                |

## Config keys

| Key                  | Description                                                                                  |
| -------------------- | -------------------------------------------------------------------------------------------- |
| `emojibase.url`      | Any host serving the `emojibase-data` package as `{url}/{locale}/data.json`                  |
| `emojibase.cache_ttl`| Cache lifetime of the emoji data, in seconds                                                 |
| `set`                | Site-wide set                                                                                |
| `sets`               | Additions or changes to the built-in sets, see [Custom sets](../guide/emoji-sets#custom-sets) |
| `disabled_sets`      | Sets to remove, e.g. `['fluent']`, or `['*']` for native only                                |
| `images.cache_path`  | Where set images are cached (default `storage/statamic-emojis/sets`)                         |
| `custom.enabled`     | Enable custom emojis                                                                         |
| `custom.container`   | Asset container for custom emojis                                                            |
| `custom.disk`        | Disk for custom emojis (`public/statamic-emojis`, unless `config/filesystems.php` defines it) |
