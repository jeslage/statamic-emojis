# Changelog

## [1.0.2](https://github.com/jeslage/statamic-emojis/compare/1.0.1...1.0.2) (2026-11-11)


### Bug Fixes

* only serve emoji images for known codes, stored once ([e82f134](https://github.com/jeslage/statamic-emojis/commit/e82f134b08413000de92e9895b628a7b2be6a395))
* remember failed Emojibase fetches, add third-party notices and support info ([4bfa19a](https://github.com/jeslage/statamic-emojis/commit/4bfa19a773181e516089923449cfb02c4c7f8318))

## [1.0.1](https://github.com/jeslage/statamic-emojis/compare/1.0.0...1.0.1) (2026-11-08)


### Bug Fixes

* zero-pad codepoints and find Noto subdivision flags. Existing installs remember these emojis as misses for up to a day; run `php please emojis:clear` to refetch them right away ([d24b03e](https://github.com/jeslage/statamic-emojis/commit/d24b03e46adeb8facdb6d0caf8df5e33a9addd59))

## 1.0.0 (2026-11-07)


### Features

* **api:** return emoji objects in the REST API and GraphQL ([3f11855](https://github.com/jeslage/statamic-emojis/commit/3f118559a979b18fb662867f28b54608e68be17a))
* **bard:** show emojis in the site-wide emoji set ([006e0f6](https://github.com/jeslage/statamic-emojis/commit/006e0f6ca1be0716a7a02d5c7d6ab4a477a6fbfc))
* **custom:** add custom emojis uploaded by editors ([5087a30](https://github.com/jeslage/statamic-emojis/commit/5087a301c4c513253df4f84a5fb53f6f44f06ffd))
* **data:** serve Emojibase data filtered by category through the CP ([31f072c](https://github.com/jeslage/statamic-emojis/commit/31f072c16f8220c27764feb3ce212a1c58dfe16f))
* **editions:** add a free and a Pro edition ([b8b1dcf](https://github.com/jeslage/statamic-emojis/commit/b8b1dcf6b9ca9254a40cf9b3ee375c4dd63e29c8))
* **emoji-text:** show emojis as images in the input with the picker button inside ([d9f2fd9](https://github.com/jeslage/statamic-emojis/commit/d9f2fd93e04b9f9b9b9694401349148f938985dd))
* **fieldtypes:** add picker-only Emoji fieldtype, rename text variant to Emoji Text ([1c27b60](https://github.com/jeslage/statamic-emojis/commit/1c27b607da18d0acc71d74e73794b8d70669419b))
* **i18n:** add English and German translations and locale mapping ([d8933ae](https://github.com/jeslage/statamic-emojis/commit/d8933ae803d5b496ed4bd657abba7152edbfeb25))
* **i18n:** add Spanish and French translations ([f28c3d8](https://github.com/jeslage/statamic-emojis/commit/f28c3d819567f1fea27a39a992d69545db0d0c04))
* **listings:** show emojis in entry listing columns ([39a9da2](https://github.com/jeslage/statamic-emojis/commit/39a9da2480b121e64e757cdbec2ae655b335af7e))
* **picker:** add custom tab, recently used and search focus ([f1ff358](https://github.com/jeslage/statamic-emojis/commit/f1ff358bcbac23496c54cbbc60f742b0539dd7c3))
* **picker:** build out the emoji picker and fieldtype settings ([6c4aa38](https://github.com/jeslage/statamic-emojis/commit/6c4aa38b79bf84d1dc876778810f9e57c2389fc1))
* **picker:** merge Smileys & People, scrollable tabs, set images ([87e781c](https://github.com/jeslage/statamic-emojis/commit/87e781ca5e8a86b1eaee3ac6b8e8d8c17c45617c))
* **sets:** add selectable emoji sets Native, Noto, Fluent and OpenMoji ([80f0cf8](https://github.com/jeslage/statamic-emojis/commit/80f0cf82cbb1ab6f8b5b008f7711f4e73e5cef9a))
* **sets:** make emoji sets extendable instead of overwritable ([69fb8ca](https://github.com/jeslage/statamic-emojis/commit/69fb8ca1c044bef743f35f4c0472851de511a472))
* **settings:** add Emojis settings page under Settings ([4ea3be4](https://github.com/jeslage/statamic-emojis/commit/4ea3be4f6f1051232728a5f033c08fce69863c1d))
* **settings:** link to Pro from the free settings page ([0fdf6aa](https://github.com/jeslage/statamic-emojis/commit/0fdf6aae91026fe6bb49d91c25c94dc36ae7e5d2))
* **settings:** pick the skin tone from a popover ([c19bf50](https://github.com/jeslage/statamic-emojis/commit/c19bf5001932525d891c6d1e4c0c1b8be7254ad1))
* **settings:** select all categories by default ([b2058bd](https://github.com/jeslage/statamic-emojis/commit/b2058bdea2325a0bc6e0f640d7cb23f483ef2569))
* **settings:** shorten field instructions ([271bbc6](https://github.com/jeslage/statamic-emojis/commit/271bbc6a562e5b21b0a054445cb61325b1f815d1))
* **settings:** show an example emoji per emoji set ([2ff9bc3](https://github.com/jeslage/statamic-emojis/commit/2ff9bc3fd139cb044fbb1634f02bc9e1ed485e59))
* **suggestions:** show shortcodes and suggest right after an emoji ([4fa208a](https://github.com/jeslage/statamic-emojis/commit/4fa208a653f1f77b447c217f1303d340c6c99e67))
* **suggestions:** suggest emojis while typing :shortcode ([29df3f5](https://github.com/jeslage/statamic-emojis/commit/29df3f580840033ae9b32e0c580038e85ff35894))
* **tags:** add the emojis tag ([232cf70](https://github.com/jeslage/statamic-emojis/commit/232cf7086bdbcf1c3073eb3cb8981a527ae86bce))


### Bug Fixes

* **bard:** align custom emojis with the text ([8929b9b](https://github.com/jeslage/statamic-emojis/commit/8929b9b6e2f3218c6fec63a1deb5c5355feca2c1))
* **picker:** prevent horizontal scrolling on long emoji names ([cc14787](https://github.com/jeslage/statamic-emojis/commit/cc147873319fb37c54fbed27f2dfdd2cf44cd3f3))
* **sets:** keep subdivision flags in one piece ([8b9f357](https://github.com/jeslage/statamic-emojis/commit/8b9f3574ddbe4d3891bc3816929d6e96c755b177))
* **suggestions:** keep the Bard suggestions at the cursor while scrolling ([6a09036](https://github.com/jeslage/statamic-emojis/commit/6a0903606d5216d691d132248013268fc898afc6))
