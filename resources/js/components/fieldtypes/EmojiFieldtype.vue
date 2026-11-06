<script setup>
// A single emoji, chosen with the picker. Empty: "Select emoji" button. Filled: the emoji (click to change) + remove button.
import { ref } from "vue";
import { Fieldtype } from "@statamic/cms";
import { Button, Popover } from "@statamic/cms/ui";
import { SmilePlus, X } from "@lucide/vue";
import EmojiPicker from "../EmojiPicker.vue";
import EmojiImage from "../EmojiImage.vue";

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { isReadOnly, update, expose } = Fieldtype.use(emit, props);
defineExpose(expose);

const open = ref(false);

function select(emoji) {
    update(emoji);
    open.value = false;
}

function remove() {
    update(null);
}
</script>

<template>
    <div v-if="isReadOnly" class="text-xl leading-none">
        <EmojiImage v-if="value" :emoji="value" :set="config.emoji_set" />
        <template v-else>–</template>
    </div>

    <div v-else class="flex items-center gap-2">
        <Popover
            v-model:open="open"
            inset
            class="w-fit! overflow-hidden"
            align="start"
        >
            <template #trigger>
                <!-- One button for both states: swapping the trigger element would leave
                     the popover anchored to the removed one, misplacing it on the next open. -->
                <Button
                    :icon-only="!!value"
                    :aria-label="value ? __('statamic-emojis::messages.change_emoji') : undefined"
                    v-tooltip="value ? __('statamic-emojis::messages.change_emoji') : null"
                    @focus="$emit('focus')"
                    @blur="$emit('blur')"
                >
                    <span v-if="value" class="text-xl leading-none"><EmojiImage :emoji="value" :set="config.emoji_set" /></span>
                    <template v-else>
                        <SmilePlus class="me-2 size-4" />
                        {{ __("statamic-emojis::messages.select_emoji") }}
                    </template>
                </Button>
            </template>
            <EmojiPicker
                :categories="config.categories ?? []"
                :set="config.emoji_set"
                :skin-tone="config.skin_tone ?? 'none'"
                :skin-tone-selector="config.skin_tone_selector ?? true"
                @select="select"
            />
        </Popover>
        <Button
            v-if="value"
            icon="x"
            icon-only
            :aria-label="__('statamic-emojis::messages.remove_emoji')"
            v-tooltip="__('statamic-emojis::messages.remove_emoji')"
            @click="remove"
        />
    </div>
</template>
