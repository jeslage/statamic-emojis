# Emoji sets

::: tip Pro
The free edition shows native emojis.
:::

Emojis are always **stored as Unicode**, so the look can change any time. **The set is site-wide**
([Emojis page](./settings) or `STATAMIC_EMOJIS_SET`, default `native`):

| Set        | Looks like              | License                                                                                    |
| ---------- | ----------------------- | ------------------------------------------------------------------------------------------ |
| `native`   | The device's own font   | –                                                                                          |
| `noto`     | Google / Android        | Apache 2.0 (flags: public domain)                                                          |
| `fluent`   | Microsoft Fluent (flat) | MIT                                                                                        |
| `openmoji` | OpenMoji                | CC BY-SA 4.0, **attribution required**: "Emoji graphics by OpenMoji, https://openmoji.org" |

Apple's emojis are proprietary; Apple users see them with `native`.

## Image cache

Image sets are SVGs the server fetches once, caches in `storage/statamic-emojis/sets` and serves from
`/!/statamic-emojis/sets/{set}/{codepoints}.svg`, so **browsers never contact a CDN**. Missing emojis fall
back to the native character.

```bash
php please emojis:warm noto      # fill the cache in advance, e.g. after deploying
php please emojis:clear noto     # empty it (without argument: all sets)
php please emojis:clear --data   # also forget the cached Emojibase data
```

See [Commands](../reference/commands) for all options.

## Custom sets

The config's `sets` **only lists additions or changes** to the built-in sets:

```php
// config/statamic-emojis.php
'sets' => [
    'twemoji' => [                       // a new set (lowercase letters only)
        'label' => 'Twemoji',            // text or translation key
        'url' => 'https://cdn.jsdelivr.net/gh/jdecked/twemoji@15.1.0/assets/svg',
        'naming' => 'fluent',
    ],
    'noto' => ['url' => 'https://emojis.example.com/noto'], // change a built-in set
],
'disabled_sets' => [],                   // e.g. ['fluent'], or ['*'] for native only
```

`naming` is how files are named (with and without FE0F are tried):

| `naming`   | File for 🧑‍🚀                                                                           |
| ---------- | ------------------------------------------------------------------------------------- |
| `fluent`   | `1f9d1-200d-1f680.svg` (default)                                                      |
| `openmoji` | `1F9D1-200D-1F680.svg`                                                                |
| `noto`     | `2D/svg/emoji_u1f9d1_200d_1f680.svg`                                                  |
| a class    | implementing `StatamicEmojis\Contracts\SetNaming` (`files(array $codepoints): array`) |

Sets can also be added from a service provider with `StatamicEmojis\EmojiSets::extend('twemoji', [...])`.

::: warning
**Check the license of sets you add** (Twemoji needs an attribution).
:::
