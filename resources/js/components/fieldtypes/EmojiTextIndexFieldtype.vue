<script setup>
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
const missing = reactive(new Set());

const parts = computed(() => {
    customEmojis.list; // re-render once custom emojis are loaded
    const all = parseEmojiText(props.value ?? "", { missing });

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
