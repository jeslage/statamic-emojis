# Templates

**Values print as plain text** (`{{ emoji }}` → 🎉). **The `emojis` modifier renders them in the
[set](./emoji-sets)**, for a single emoji, text or HTML (only text between tags is replaced).

## Modifier

```antlers
{{ content | emojis }}               {{# site-wide set #}}
{{ content | emojis('openmoji') }}   {{# explicit set #}}

{{ emoji:label }}                    {{# party popper #}}
{{ emoji:url }}                      {{# image URL, empty for native #}}
```

```blade
{!! Statamic::modify($page->content)->emojis() !!}
```

## Tag

The `emojis` tag does the same and loops over [custom emojis](./custom-emojis) (`shortcode`, `emoji`,
`label`, `url`, `keywords`):

```antlers
{{ emojis :value="content" set="noto" }}
{{ emojis:url value="🎉" }}
{{ emojis:label value="🎉" }}

{{ emojis:custom }}
    <img src="{{ url }}" alt="{{ label }}"> {{ emoji }}
{{ /emojis:custom }}
```

```blade
<s:emojis :value="$page->content" />
```

## Styling

Images get `class="emoji"`; size them with CSS, e.g.

```css
img.emoji {
    width: 1.15em;
    height: 1.15em;
    vertical-align: -0.2em;
}
```
