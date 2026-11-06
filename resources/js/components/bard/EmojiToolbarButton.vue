<script setup>
import { Button, Popover } from "@statamic/cms/ui";
import { SmilePlus } from "@lucide/vue";
import EmojiPicker from "../EmojiPicker.vue";
import { isShortcode } from "../../custom-emojis.js";
import { pickerOptions } from "../../settings.js";

const props = defineProps({
    button: Object,
    active: Boolean,
    variant: String,
    config: Object,
    bard: {},
    editor: {},
});

function insert(emoji) {
    // Custom emojis become an inline image node, Unicode emojis plain text
    const content = isShortcode(emoji)
        ? { type: "customEmoji", attrs: { shortcode: emoji.slice(1, -1) } }
        : emoji;

    props.editor.chain().focus().insertContent(content).run();
}
</script>

<template>
    <Popover inset class="w-fit! overflow-hidden" align="start">
        <template #trigger>
            <Button
                class="px-2!"
                :variant="variant === 'floating' ? 'subtle' : 'ghost'"
                size="sm"
                :aria-label="button.text"
                v-tooltip="button.text"
                @mousedown.prevent
            >
                <SmilePlus class="size-4" />
            </Button>
        </template>
        <EmojiPicker v-bind="pickerOptions()" @select="insert" />
    </Popover>
</template>
