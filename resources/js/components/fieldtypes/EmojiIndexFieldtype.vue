<script setup>
import EmojiImage from "../EmojiImage.vue";

defineProps({
    handle: { type: String, default: null },
    value: { type: String, default: null },
    values: { type: Object, default: () => ({}) },
});
</script>

<template>
    <span v-if="value" class="inline-block align-middle text-lg leading-none" :title="value">
        <EmojiImage :emoji="value" />
    </span>
</template>
