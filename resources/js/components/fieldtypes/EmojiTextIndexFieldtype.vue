<script setup>
// Emoji Text field in entry listings: the text (with prepend/append, shortened like Statamic's default
// column) with custom emojis and Unicode emojis as images, like in the field.
import { computed, reactive } from "vue";
import { useCustomEmojis } from "../../custom-emojis.js";
import { parseEmojiText, partText } from "../../emoji-text.js";

const props = defineProps({
    handle: { type: String, default: null },
    value: { type: String, default: null },
    values: { type: Object, default: () => ({}) },
});

const TRUNCATE_AT = 50;
const customEmojis = useCustomEmojis();
// Emojis the set has no image for: shown as native characters
const missing = reactive(new Set());

const parts = computed(() => {
    customEmojis.list; // re-render once custom emojis are loaded
    const all = parseEmojiText(props.value ?? "", { missing });

    // Shorten to TRUNCATE_AT characters of the text without cutting an emoji
    const shown = [];
    let budget = TRUNCATE_AT;
    for (const part of all) {
        const length = partText(part).length;
        if (length <= budget) {
            shown.push(part);
            budget -= length;
            continue;
        }
        if ("text" in part && budget > 0) shown.push({ text: part.text.slice(0, budget) });
        shown.push({ text: "…" });
        break;
    }
    return shown;
});
</script>

<template>
    <span v-if="value" :title="value">
        <template v-for="(part, index) in parts" :key="index">
            <template v-if="'text' in part">{{ part.text }}</template>
            <img
                v-else
                :src="part.url"
                :alt="partText(part)"
                class="emoji-index-image"
                draggable="false"
                @error="part.emoji && missing.add(part.emoji)"
            />
        </template>
    </span>
</template>
