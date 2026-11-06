<script setup>
// Emojis page: the uploaded custom emojis as a grid, with a link to the asset container.
import { Fieldtype } from "@statamic/cms";
import { Button } from "@statamic/cms/ui";
import { Upload } from "@lucide/vue";

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose } = Fieldtype.use(emit, props);
defineExpose(expose);
</script>

<template>
    <div class="space-y-4">
        <div class="text-sm font-medium text-gray-925 dark:text-gray-100">{{ __(config.display) }}</div>
        <p v-if="!meta.container_url" class="text-sm text-gray-600 dark:text-gray-400">
            {{ __("statamic-emojis::messages.settings.custom_setup", { command: meta.setup_command }) }}
        </p>

        <template v-else>
            <p v-if="!meta.emojis.length" class="text-sm text-gray-600 dark:text-gray-400">
                {{ __("statamic-emojis::messages.settings.custom_empty") }}
            </p>

            <ul v-else class="grid grid-cols-[repeat(auto-fill,minmax(7rem,1fr))] gap-2">
                <li
                    v-for="emoji in meta.emojis"
                    :key="emoji.shortcode"
                    class="flex flex-col items-center gap-1.5 rounded-lg border border-gray-200 px-2 py-3 text-center dark:border-gray-700"
                >
                    <img :src="emoji.url" :alt="emoji.label" class="size-10 object-contain" draggable="false" />
                    <span class="w-full truncate text-xs font-medium text-gray-800 dark:text-gray-200" :title="emoji.label">
                        {{ emoji.label }}
                    </span>
                    <!-- Plain span: <code> gets the CP's global code styling (tinted box, colored text) -->
                    <span
                        class="w-full truncate font-mono text-[0.6875rem] leading-4 text-gray-500 dark:text-gray-400"
                        :title="`:${emoji.shortcode}:`"
                    >
                        :{{ emoji.shortcode }}:
                    </span>
                </li>
            </ul>

            <Button :href="meta.container_url" size="sm">
                <Upload class="me-2 size-3.5" />
                {{ __("statamic-emojis::messages.settings.custom_manage") }}
            </Button>
        </template>
    </div>
</template>
