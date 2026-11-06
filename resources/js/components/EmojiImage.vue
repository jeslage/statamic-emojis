<script setup>
// An emoji in the configured set: an <img> for image sets, the plain character for native.
import { computed } from "vue";
import { emojiImageUrl } from "../emojibase.js";

const props = defineProps({
    emoji: { type: String, required: true },
    set: { type: String, default: null },
});

const src = computed(() => emojiImageUrl(props.emoji, props.set));
</script>

<template>
    <img
        v-if="src"
        :src="src"
        :alt="emoji"
        class="emoji-image inline-block size-[1em] align-[-0.125em]"
        draggable="false"
        loading="lazy"
        decoding="async"
    />
    <span v-else class="emoji-picker-native">{{ emoji }}</span>
</template>
