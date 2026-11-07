<script setup>
import { computed } from "vue";
import { Fieldtype } from "@statamic/cms";
import { Select } from "@statamic/cms/ui";
import EmojiImage from "../EmojiImage.vue";

const EXAMPLE = "😀";

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { isReadOnly, update, expose } = Fieldtype.use(emit, props);
defineExpose(expose);

const options = computed(() =>
    Object.entries(props.config.options ?? {}).map(([value, label]) => ({ value, label: __(label) })),
);
</script>

<template>
    <Select :model-value="value" :options="options" :read-only="isReadOnly" @update:model-value="update">
        <template #selected-option="{ option }">
            <span class="flex items-center gap-2">
                <EmojiImage :emoji="EXAMPLE" :set="option.value" class="text-base leading-none" />
                {{ option.label }}
            </span>
        </template>
        <template #option="option">
            <span class="flex items-center gap-2">
                <EmojiImage :emoji="EXAMPLE" :set="option.value" class="text-base leading-none" />
                {{ option.label }}
            </span>
        </template>
    </Select>
</template>
