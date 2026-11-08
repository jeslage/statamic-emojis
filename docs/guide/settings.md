# Settings

**Settings → Emojis** in the Control Panel (the "Statamic Emojis settings" permission, under Addons
in the role editor), saved to `resources/addons/statamic-emojis.yaml`:

- **General:** emoji set, picker defaults, recently used emojis and shortcode suggestions
- **Custom Emojis** (Pro): turn them on or off, see and upload them
- **Image Cache** (Pro): cache size per set, fill or clear it

## Precedence

**A field's own setting wins, then the Emojis page, then `config/statamic-emojis.php`** (and its `.env`
variables). Publish the config with:

```bash
php artisan vendor:publish --tag=statamic-emojis-config
```

All options and environment variables are listed under [Configuration](../reference/configuration).
