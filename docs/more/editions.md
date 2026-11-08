# Editions

| Feature                                                             | Free | Pro |
| ------------------------------------------------------------------- | :--: | :-: |
| Emoji and Emoji Text fieldtypes, Bard button                        |  ✅   |  ✅  |
| Shortcode suggestions, recently used, skin tones                    |  ✅   |  ✅  |
| `emojis` modifier and tag, REST & GraphQL                           |  ✅   |  ✅  |
| Native emojis                                                       |  ✅   |  ✅  |
| [Emoji sets](../guide/emoji-sets): Noto, Fluent, OpenMoji, your own |  –   |  ✅  |
| [Custom emojis](../guide/custom-emojis)                             |  –   |  ✅  |

The free edition is the default. **Pro needs a license from the
[Statamic Marketplace](https://statamic.com/addons)** for each site in production; enable it in
`config/statamic/editions.php`:

```php
'addons' => [
    'jeslage/statamic-emojis' => 'pro',
],
```

See [License & credits](./credits) for the license terms.
