<script setup>
// Footer of the picker: the hovered emoji in large with its name, and the skin tone toggle.
import EmojiImage from "./EmojiImage.vue";
import SkinToneButton from "./SkinToneButton.vue";

defineProps({
    emoji: { type: String, default: null },
    label: { type: String, default: null },
    skinToneSelector: { type: Boolean, default: true },
    skinTone: { type: String, default: "none" },
});

const emit = defineEmits(["update:skinTone"]);
</script>

<template>
    <!-- w-0 min-w-full: the footer takes the picker's width but never widens it, so long names truncate -->
    <div
        class="flex h-12 w-0 min-w-full shrink-0 items-center gap-2 border-t border-gray-200 px-3 dark:border-gray-700"
    >
        <template v-if="emoji">
            <span class="emoji-picker-preview shrink-0 text-3xl leading-none" aria-hidden="true">
                <EmojiImage :emoji="emoji" />
            </span>
            <span class="min-w-0 flex-1 truncate text-sm text-gray-700 dark:text-gray-300" :title="label">
                {{ label }}
            </span>
        </template>
        <span v-else class="min-w-0 flex-1 truncate text-sm text-gray-500 dark:text-gray-400">
            {{ __("statamic-emojis::messages.pick_an_emoji") }}
        </span>

        <SkinToneButton
            v-if="skinToneSelector"
            class="ms-auto shrink-0"
            size="sm"
            :model-value="skinTone"
            @update:model-value="emit('update:skinTone', $event)"
        />
    </div>
</template>
