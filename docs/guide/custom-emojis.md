# Custom emojis

::: tip Pro
Custom emojis are part of Statamic Emojis Pro.
:::

Editors can upload images (PNG, GIF, WebP or SVG, up to 1 MB) and use them by **shortcode**, e.g.
`:party-parrot:`. **Run `php please emojis:custom-setup` once**, then upload via
**Settings → Emojis → Custom Emojis**.

- **Fields:** each image has a **Name**, an optional **Shortcode** (defaults to the filename) and
  **Keywords**. Folders are only for organizing: `halloween/ghost.svg` is `:ghost:`.
- **Picker:** a "Custom" tab, and matches above the search results.
- **Values:** Emoji and Emoji Text store `:shortcode:`; Bard inserts an image node and renders it as
  `<img class="emoji emoji-custom">`. **Use the [`emojis` modifier](./templates) for the other fields.**

## Shortcodes

Shortcodes are lowercase letters, numbers, `-`, `_` and `+`.

::: warning
**Renaming a shortcode breaks existing references.**
:::

## Storage

Images are stored on the `statamic-emojis` disk (`public/statamic-emojis`, unless you
define one in `config/filesystems.php`). Configure with `STATAMIC_EMOJIS_CUSTOM`,
`STATAMIC_EMOJIS_CUSTOM_CONTAINER` and `STATAMIC_EMOJIS_CUSTOM_DISK` (see
[Configuration](../reference/configuration)).
