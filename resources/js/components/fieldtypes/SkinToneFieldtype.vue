<script setup>
// Skin tone setting: a button with 👋 in the current tone, opening a popover with one button per tone.
// In fields' config (`default_label` set), "Default (…)" stands for no value, i.e. the site-wide setting.
import { computed, ref } from "vue";
import { Fieldtype } from "@statamic/cms";
import { Button, Popover } from "@statamic/cms/ui";
import EmojiImage from "../EmojiImage.vue";
import { SKIN_TONE_MODIFIERS, SKIN_TONES } from "../../skin-tones.js";
import { siteSettings } from "../../settings.js";

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { isReadOnly, update, expose } = Fieldtype.use(emit, props);
defineExpose(expose);

const open = ref(false);
const inherits = computed(() => !!props.config.default_label);

// Selected tone, null = default (fields only)
const current = computed(() =>
    SKIN_TONES.includes(props.value) ? props.value : inherits.value ? null : "none",
);
// Tone shown on the trigger: the selected one, or the site-wide one for "Default"
const shown = computed(() => current.value ?? siteSettings().skinTone ?? "none");

const hand = (tone) => `👋${SKIN_TONE_MODIFIERS[tone] ?? ""}`;
const label = (tone) => __(`statamic-emojis::messages.skin_tones.${tone}`);

function select(tone) {
    update(tone);
    open.value = false;
}
</script>

<template>
    <div v-if="isReadOnly" class="flex items-center gap-2">
        <span class="text-lg leading-none"><EmojiImage :emoji="hand(shown)" /></span>
        {{ current ? label(current) : config.default_label }}
    </div>

    <Popover v-else v-model:open="open" class="w-fit!" align="start">
        <template #trigger>
            <Button @focus="$emit('focus')" @blur="$emit('blur')">
                <span class="me-2 text-lg leading-none"><EmojiImage :emoji="hand(shown)" /></span>
                {{ current ? label(current) : config.default_label }}
            </Button>
        </template>

        <div class="flex items-center gap-1">
            <Button
                v-if="inherits"
                size="sm"
                :variant="current === null ? 'ghost-pressed' : 'ghost'"
                :text="config.default_label"
                @click="select(null)"
            />
            <Button
                v-for="tone in SKIN_TONES"
                :key="tone"
                size="sm"
                icon-only
                :variant="current === tone ? 'ghost-pressed' : 'ghost'"
                :aria-label="label(tone)"
                :aria-pressed="current === tone"
                v-tooltip="label(tone)"
                @click="select(tone)"
            >
                <span class="text-lg leading-none"><EmojiImage :emoji="hand(tone)" /></span>
            </Button>
        </div>
    </Popover>
</template>
