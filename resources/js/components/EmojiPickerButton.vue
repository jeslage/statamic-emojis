<script setup>
import { Button, Popover } from "@statamic/cms/ui";
import { SmilePlus } from "@lucide/vue";
import EmojiPicker from "./EmojiPicker.vue";

defineProps({
    options: { type: Object, default: () => ({}) },
    inset: { type: Boolean, default: false },
});

const emit = defineEmits(["select"]);
</script>

<template>
    <Popover inset class="w-fit! overflow-hidden" align="end">
        <template #trigger>
            <!-- Statamic's icon set has no smiley, so use Lucide's -->
            <Button
                icon-only
                :size="inset ? 'sm' : 'base'"
                :variant="inset ? 'ghost' : 'default'"
                :aria-label="__('statamic-emojis::messages.insert_emoji')"
                v-tooltip="__('statamic-emojis::messages.insert_emoji')"
            >
                <SmilePlus class="size-4" />
            </Button>
        </template>
        <EmojiPicker v-bind="options" @select="emit('select', $event)" />
    </Popover>
</template>
