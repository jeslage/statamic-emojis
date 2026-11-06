---
id: 2f6e8a90-1b3c-4d5e-8f70-a1b2c3d4e5f6
blueprint: pages
title: 'Emoji Fields (Blade)'
template: emoji-fields-blade
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
