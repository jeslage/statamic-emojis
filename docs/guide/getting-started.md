# Installation

```bash
composer require jeslage/statamic-emojis
php please emojis:custom-setup   # Pro, optional: asset container for custom emojis
```

Releases ship the built Control Panel assets, which
[`pixelfear/composer-dist-plugin`](https://github.com/pixelfear/composer-dist-plugin) downloads into
`resources/dist`. **For a development branch, build them yourself** (`npm ci && npm run build`).

Next: add an [emoji field](./fieldtypes) to a blueprint, or pick the site-wide defaults under
[Settings → Emojis](./settings).

Some features need **Pro**, see [Editions](../more/editions).
