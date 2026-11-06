<script setup>
// Emojis page: cached images per emoji set, with buttons to fill (in chunks, with progress) and clear.
import { reactive, ref } from "vue";
import { Fieldtype } from "@statamic/cms";
import { Button } from "@statamic/cms/ui";

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose } = Fieldtype.use(emit, props);
defineExpose(expose);

const status = ref(props.meta.status ?? []);
const busy = reactive({}); // set => { action, percent, failed }

async function request(url, body = null) {
    const response = await fetch(url, {
        method: body ? "POST" : "GET",
        headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": Statamic.$config.get("csrfToken"),
        },
        credentials: "same-origin",
        body: body ? JSON.stringify(body) : undefined,
    });

    if (!response.ok) throw new Error(`${response.status} ${response.statusText}`);
    return response.json();
}

const urlFor = (key, set) => props.meta.urls[key].replace("__SET__", set);

async function refresh() {
    status.value = await request(props.meta.urls.status);
}

async function warm(set) {
    busy[set] = { action: "warm", percent: 0, failed: 0 };

    try {
        let offset = 0;
        let done = false;

        while (!done) {
            const result = await request(urlFor("warm", set), { offset, limit: 50 });
            offset = result.next;
            done = result.done;
            busy[set].failed += result.failed;
            busy[set].percent = Math.round((result.next / result.total) * 100);
        }

        Statamic.$toast.success(__("statamic-emojis::messages.settings.cache_warm") + " ✓");
    } catch (error) {
        Statamic.$toast.error(error.message);
    } finally {
        const failed = busy[set].failed;
        delete busy[set];
        await refresh();
        if (failed) Statamic.$toast.info(__("statamic-emojis::messages.settings.cache_failed", { count: failed }));
    }
}

async function clear(set) {
    busy[set] = { action: "clear" };

    try {
        await request(urlFor("clear", set), {});
    } catch (error) {
        Statamic.$toast.error(error.message);
    } finally {
        delete busy[set];
        await refresh();
    }
}

function size(bytes) {
    if (bytes < 1024 * 1024) return `${Math.round(bytes / 1024)} KB`;
    return `${(bytes / 1024 / 1024).toFixed(1)} MB`;
}
</script>

<template>
    <div class="divide-y divide-gray-200 dark:divide-gray-700">
        <div v-for="item in status" :key="item.set" class="flex flex-wrap items-center gap-x-4 gap-y-2 py-3">
            <div class="min-w-32 font-medium">{{ item.label }}</div>

            <div class="min-w-0 flex-1 text-sm text-gray-600 dark:text-gray-400">
                <template v-if="busy[item.set]?.action === 'warm'">
                    <div>{{ __("statamic-emojis::messages.settings.cache_warming", { percent: busy[item.set].percent }) }}</div>
                    <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                        <div class="h-full bg-blue-500 transition-all" :style="{ width: `${busy[item.set].percent}%` }" />
                    </div>
                </template>
                <template v-else>
                    {{
                        __("statamic-emojis::messages.settings.cache_cached", {
                            cached: item.cached,
                            total: item.total ?? "–",
                        })
                    }}
                    · {{ __("statamic-emojis::messages.settings.cache_size", { size: size(item.size) }) }}
                </template>
            </div>

            <div class="flex gap-2">
                <Button size="sm" :disabled="!!busy[item.set]" :loading="busy[item.set]?.action === 'warm'" @click="warm(item.set)">
                    {{ __("statamic-emojis::messages.settings.cache_warm") }}
                </Button>
                <Button
                    size="sm"
                    variant="ghost"
                    :disabled="!!busy[item.set] || !item.cached"
                    :loading="busy[item.set]?.action === 'clear'"
                    @click="clear(item.set)"
                >
                    {{ __("statamic-emojis::messages.settings.cache_clear") }}
                </Button>
            </div>
        </div>
    </div>
</template>
