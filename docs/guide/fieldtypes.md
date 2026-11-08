# Fieldtypes

- **Emoji** (`type: emoji`): a single emoji, stored as a string (`null` when removed).
- **Emoji Text** (`type: emoji_text`): the core Text field (placeholder, character limit, prepend, …)
  with a picker button. Custom emojis show as images.
- **Bard**: enable the `emoji` button in the field's toolbar config.

## Settings

Both fieldtypes have these settings. **Empty values fall back to the [site-wide settings](./settings)**:

| Setting              | Default | Description                                                             |
| -------------------- | ------- | ----------------------------------------------------------------------- |
| `categories`         | all     | Categories the picker offers, e.g. `[smileys-people, animals-nature]`   |
| `skin_tone`          | `none`  | `none`, `light`, `medium-light`, `medium`, `medium-dark` or `dark`      |
| `skin_tone_selector` | `true`  | Show a skin tone button (the editor's choice is remembered per browser) |
| `custom_emojis`      | `true`  | Offer [custom emojis](./custom-emojis) (Pro)                            |

Categories: `smileys-people`, `animals-nature`, `food-drink`, `travel-places`, `activities`,
`objects`, `symbols`, `flags`.

## Listings

In entry listings, listable emoji fields show their emojis like the field does.

## Output

Values are plain Unicode. See [Templates](./templates) to print or render them, and
[API](../reference/api) for REST and GraphQL.
