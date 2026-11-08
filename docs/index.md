---
layout: home

hero:
    name: Statamic Emojis
    text: An emoji picker for the Control Panel
    tagline: Emoji fieldtypes, a Bard button, shortcode suggestions and, with Pro, emoji sets and custom emoji uploads. Stored as Unicode, rendered however you like.
    actions:
        - theme: brand
          text: Get started
          link: /guide/getting-started
        - theme: alt
          text: View on GitHub
          link: https://github.com/jeslage/statamic-emojis

features:
    - icon: 😀
      title: Emoji fieldtype
      details: Pick a single emoji.
      link: /guide/fieldtypes
    - icon: ✍️
      title: Emoji Text fieldtype
      details: A text field with an emoji picker that inserts at the cursor.
      link: /guide/fieldtypes
    - icon: 📝
      title: Bard button
      details: Insert emojis into Bard.
      link: /guide/fieldtypes
    - icon: 🔍
      title: Shortcode suggestions
      details: Type <code>:rock</code> to get matching emojis.
      link: /guide/shortcode-suggestions
    - icon: 🎨
      title: Emoji sets (Pro)
      details: Noto, Fluent, OpenMoji or your own, served from your server.
      link: /guide/emoji-sets
    - icon: 🦜
      title: Custom emojis (Pro)
      details: Upload your own images and use them as <code>:party-parrot:</code>.
      link: /guide/custom-emojis
    - icon: 🏷️
      title: Modifier and tag
      details: Render emojis in Antlers and Blade.
      link: /guide/templates
    - icon: 🔌
      title: REST & GraphQL
      details: Emoji values with name and image URL.
      link: /reference/api
    - icon: 🌍
      title: Translated
      details: Picker in en, de, es and fr, emoji names in the editor's language.
      link: /guide/translations
---

<div class="vp-doc" style="margin-top: 48px">

![Two Control Panel screenshots: an entry with the emoji picker open on an Emoji Text field, and shortcode suggestions for ":sunrise" in a Bard field](/demo.png)

The picker is built on [vue-frimousse](https://vue-frimousse.robertshaw.id) and uses
[Emojibase](https://emojibase.dev) data, with category tabs, search, skin tones and recently used emojis.

</div>
