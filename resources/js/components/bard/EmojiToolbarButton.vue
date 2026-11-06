<script setup>
import { Button, Popover } from "@statamic/cms/ui";
import { SmilePlus } from "@lucide/vue";
import EmojiPicker from "../EmojiPicker.vue";

const props = defineProps({
    button: Object,
    active: Boolean,
    variant: String,
    config: Object,
    bard: {},
    editor: {},
});

function insert(emoji) {
    props.editor.chain().focus().insertContent(emoji).run();
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
        <EmojiPicker @select="insert" />
    </Popover>
</template>
