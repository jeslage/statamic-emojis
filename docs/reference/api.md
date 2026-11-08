# API (REST & GraphQL)

Emoji fields are objects in the API:

```json
"mood": {
    "value": "👍🏻",
    "label": "thumbs up: light skin tone",
    "url": "https://example.com/!/statamic-emojis/sets/openmoji/1f44d-1f3fb.svg",
    "set": "openmoji",
    "custom": false,
    "shortcode": null
}
```

Emoji Text fields are:

```json
"motto": {
    "value": "Done 👍",
    "html": "Done <img class=\"emoji\" …>",
    "set": "noto"
}
```

## GraphQL

GraphQL has the matching types `EmojiValue` and `EmojiTextValue`:

```graphql
mood { value label url set custom shortcode }
motto { value html set }
```
