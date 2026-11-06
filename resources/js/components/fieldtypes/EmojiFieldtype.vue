<script setup>
import { Fieldtype } from '@statamic/cms';
import { Input, Popover } from '@statamic/cms/ui';
import EmojiPicker from '../EmojiPicker.vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update, isReadOnly } = Fieldtype.use(emit, props);
defineExpose(expose);

function insert(emoji) {
    // TODO: insert at cursor position instead of appending
    update((props.value ?? '') + emoji);
}
</script>

<template>
    <div class="flex items-center gap-2">
        <!-- TODO: pass through the remaining Text config (input_type, prepend, append, character_limit, ...) -->
        <Input
            class="flex-1"
            :model-value="value"
            :read-only="isReadOnly"
            :placeholder="__(config.placeholder)"
            :limit="config.character_limit"
            @update:model-value="update"
        />
        <!-- TODO: verify Popover trigger/slot API -->
        <Popover v-if="!isReadOnly">
            <template #trigger>
                <button type="button" :aria-label="__('Insert emoji')">🙂</button>
            </template>
            <EmojiPicker @select="insert" />
        </Popover>
    </div>
</template>
