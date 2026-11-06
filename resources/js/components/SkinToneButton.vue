<script setup>
// Cycles through the skin tones (v-model). Independent of frimousse's store, so it can live outside <EmojiPicker.Root>.
import { computed } from "vue";
import { Button } from "@statamic/cms/ui";

// Emoji modifiers per skin tone (Unicode Fitzpatrick scale)
const MODIFIERS = {
    none: "",
    light: "\u{1F3FB}",
    "medium-light": "\u{1F3FC}",
    medium: "\u{1F3FD}",
    "medium-dark": "\u{1F3FE}",
    dark: "\u{1F3FF}",
};
const TONES = Object.keys(MODIFIERS);

const props = defineProps({
    modelValue: { type: String, default: "none" },
    size: { type: String, default: "xs" },
});

const emit = defineEmits(["update:modelValue"]);

const current = computed(() =>
    TONES.includes(props.modelValue) ? props.modelValue : "none",
);
const label = computed(
    () =>
        `${__("statamic-emojis::messages.change_skin_tone")} (${__(`statamic-emojis::messages.skin_tones.${current.value}`)})`,
);

function next() {
    emit(
        "update:modelValue",
        TONES[(TONES.indexOf(current.value) + 1) % TONES.length],
    );
}
</script>

<template>
    <Button
        icon-only
        :size="size"
        variant="default"
        :aria-label="label"
        v-tooltip="label"
        @click="next"
    >
        <span class="text-sm leading-none">✋{{ MODIFIERS[current] }}</span>
    </Button>
</template>
