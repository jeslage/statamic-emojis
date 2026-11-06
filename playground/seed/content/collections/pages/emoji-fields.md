---
id: 7d3c2f1e-5a4b-4c6d-9e8f-0a1b2c3d4e5f
blueprint: pages
title: 'Emoji Fields'
template: emoji-fields
emoji: 🎉
emoji_configured: 😎
emoji_read_only: 🔒
emoji_required: ✅
emoji_text: 'Party time 🎉'
emoji_text_configured: '😎 relaxed'
emoji_text_required: 'Done ✅'
content:
  -
    type: heading
    attrs:
      level: 2
    content:
      -
        type: text
        text: 'Hello 👋'
  -
    type: paragraph
    content:
      -
        type: text
        text: 'Place the cursor here and use the emoji button in the toolbar 🚀'
content_without_emoji:
  -
    type: paragraph
    content:
      -
        type: text
        text: 'No emoji button in this toolbar.'
blocks:
  -
    id: callout-1
    type: callout
    enabled: true
    icon: 💡
    text:
      -
        type: paragraph
        content:
          -
            type: text
            text: 'A callout inside a replicator.'
reactions:
  -
    id: reaction-1
    emoji: 👍
    label: Like
  -
    id: reaction-2
    emoji: ❤️
    label: Love
---
