# Emoji data

The picker loads [Emojibase](https://emojibase.dev) data through the Control Panel
(`/cp/statamic-emojis/emojibase/…`). The server fetches it once from jsDelivr and caches it:

```ini
STATAMIC_EMOJIS_EMOJIBASE_URL=https://cdn.jsdelivr.net/npm/emojibase-data@18
STATAMIC_EMOJIS_CACHE_TTL=604800
```

Point the URL at a self-hosted copy if the server can't reach the CDN; it must serve
`{url}/{locale}/data.json`. Clear the cache with `php please emojis:clear --data`.
