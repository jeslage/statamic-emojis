<script setup>
// An emoji for display: custom emojis (":shortcode:") as their uploaded image,
// Unicode emojis as an <img> of the image set, or the plain character for native.
import { computed } from "vue";
import { emojiImageUrl } from "../emojibase.js";
import { findCustom, isShortcode, useCustomEmojis } from "../custom-emojis.js";

const props = defineProps({
    emoji: { type: String, required: true },
});

const customEmojis = useCustomEmojis();

const custom = computed(() =>
    isShortcode(props.emoji) && customEmojis.loaded ? findCustom(props.emoji) : null,
);
const src = computed(() =>
    isShortcode(props.emoji) ? null : emojiImageUrl(props.emoji),
);
</script>

<template>
    <img
        v-if="custom"
        :src="custom.url"
        :alt="custom.label"
        :title="emoji"
        class="emoji-image emoji-custom inline-block size-[1em] object-contain align-[-0.125em]"
        draggable="false"
    />
    <img
        v-else-if="src"
        :src="src"
        :alt="emoji"
        class="emoji-image inline-block size-[1em] align-[-0.125em]"
        draggable="false"
        loading="lazy"
        decoding="async"
    />
    <span v-else class="emoji-picker-native">{{ emoji }}</span>
</template>
