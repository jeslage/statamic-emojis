# Commands

All commands run with `php please` (or `php artisan`).

## `emojis:custom-setup`

::: tip Pro
:::

Creates the asset container and blueprint for [custom emojis](../guide/custom-emojis). Run it once.

```bash
php please emojis:custom-setup
```

## `emojis:warm`

::: tip Pro
:::

Fetches the images of an [emoji set](../guide/emoji-sets) into the cache in advance, e.g. after deploying.

```bash
php please emojis:warm               # the site-wide set
php please emojis:warm noto
php please emojis:warm noto --concurrency=20
```

| Argument / option | Description                                    |
| ----------------- | ---------------------------------------------- |
| `set`             | Image set to fetch (default: the site-wide set) |
| `--concurrency`   | Parallel downloads (default `10`)               |

## `emojis:clear`

Clears the cached images of an emoji set (Pro) and, with `--data`, the cached [Emojibase data](./emoji-data)
(also in the free edition).

```bash
php please emojis:clear              # all image sets
php please emojis:clear noto
php please emojis:clear --data       # also forget the cached Emojibase data
```

| Argument / option | Description                                                                   |
| ----------------- | ----------------------------------------------------------------------------- |
| `set`             | Image set to clear (default: all image sets)                                  |
| `--data`          | Also forget the cached Emojibase data (emoji names, categories, search keywords) |
