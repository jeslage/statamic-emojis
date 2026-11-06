<script setup>
import { Button, Popover } from '@statamic/cms/ui';
import EmojiPicker from '../EmojiPicker.vue';

// Props passed by Bard to custom toolbar button components
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
    <!-- TODO: verify Popover trigger/slot API -->
    <Popover>
        <template #trigger>
            <Button
                class="px-2!"
                :variant="variant === 'floating' ? 'subtle' : 'ghost'"
                size="sm"
                :aria-label="button.text"
                v-tooltip="button.text"
                @mousedown.prevent
                v-html="button.html"
            />
        </template>
        <EmojiPicker @select="insert" />
    </Popover>
</template>
