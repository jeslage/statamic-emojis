<script setup>
// Shortcode suggestions list (Emoji Text and Bard). Uses no Statamic globals, as Bard mounts it in its own app.
// mousedown (not click) selects, so the input or editor keeps its focus.
import EmojiImage from "./EmojiImage.vue";

defineProps({
    items: { type: Array, required: true },
    active: { type: Number, default: 0 },
    set: { type: String, default: null },
});

const emit = defineEmits(["select", "hover"]);
</script>

<template>
    <ul
        role="listbox"
        class="emoji-suggestions w-72 overflow-hidden rounded-lg border border-gray-200 bg-white p-1 shadow-lg dark:border-gray-700 dark:bg-gray-800"
    >
        <li
            v-for="(item, index) in items"
            :key="item.emoji"
            role="option"
            :aria-selected="index === active"
            class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 text-sm text-gray-800 dark:text-gray-200"
            :class="{ 'bg-gray-100 dark:bg-gray-700': index === active }"
            @mousedown.prevent="emit('select', item)"
            @mouseenter="emit('hover', index)"
        >
            <span class="emoji-picker-preview shrink-0 text-lg leading-none"><EmojiImage :emoji="item.emoji" /></span>
            <span class="min-w-0 flex-1 truncate" :title="item.label">{{ item.shortcode ? `:${item.shortcode}:` : item.label }}</span>
        </li>
    </ul>
</template>
