<script setup>
// Shared emoji picker used by both the fieldtype and the Bard toolbar button.
// Built on vue-frimousse (https://vue-frimousse.robertshaw.id), which is unstyled and composable.
import { computed, ref, watch } from "vue";
import EmojiPicker from "vue-frimousse";
import { Button } from "@statamic/cms/ui";
import {
    Flag,
    Lightbulb,
    PersonStanding,
    Plane,
    Hamburger,
    Search,
    Smile,
    RugbyBall,
    Pilcrow,
    Leaf,
} from "@lucide/vue";
import { cpEmojibaseLocale, emojibaseLocale } from "../locale.js";
import {
    CATEGORIES,
    emojibaseUrl,
    prepareFrimousseCache,
} from "../emojibase.js";
import SkinToneButton from "./SkinToneButton.vue";

const SKIN_TONES = [
    "none",
    "light",
    "medium-light",
    "medium",
    "medium-dark",
    "dark",
];
const STORAGE_KEY = "statamic-emojis/skin-tone";

const CATEGORY_ICONS = {
    "smileys-emotion": Smile,
    "people-body": PersonStanding,
    "animals-nature": Leaf,
    "food-drink": Hamburger,
    "travel-places": Plane,
    activities: RugbyBall,
    objects: Lightbulb,
    symbols: Pilcrow,
    flags: Flag,
};

const props = defineProps({
    // Emojibase locale for category names, emoji labels and search. Defaults to the CP locale.
    locale: { type: String, default: null },
    columns: { type: Number, default: 9 },
    // Default skin tone (field config `skin_tone`)
    skinTone: { type: String, default: "none" },
    // Show a button to change the skin tone (field config `skin_tone_selector`)
    skinToneSelector: { type: Boolean, default: true },
    // Allowed category keys (field config `categories`), empty = all
    categories: { type: Array, default: () => [] },
});

const emit = defineEmits(["select"]);

// With the selector, the editor's last choice (per browser) wins over the configured default.
function rememberedSkinTone() {
    try {
        const tone = localStorage.getItem(STORAGE_KEY);
        return SKIN_TONES.includes(tone) ? tone : null;
    } catch {
        return null;
    }
}

const chosenSkinTone = ref(
    props.skinToneSelector ? rememberedSkinTone() : null,
);
const pickerSkinTone = computed(
    () =>
        chosenSkinTone.value ??
        (SKIN_TONES.includes(props.skinTone) ? props.skinTone : "none"),
);

function onSkinToneChange(tone) {
    chosenSkinTone.value = tone;
    try {
        localStorage.setItem(STORAGE_KEY, tone);
    } catch {
        // Storage unavailable (private mode etc.): keep the choice for this picker only
    }
}

const pickerLocale = computed(() =>
    props.locale ? emojibaseLocale(props.locale) : cpEmojibaseLocale(),
);

// Tabs: "search" (all allowed categories, with search) + one per allowed category (only that category)
const allowedCategories = computed(() =>
    props.categories?.length
        ? CATEGORIES.filter((key) => props.categories.includes(key))
        : CATEGORIES,
);
const tab = ref("search");
const tabs = computed(() => [
    {
        key: "search",
        icon: Search,
        label: __("statamic-emojis::messages.search_tab"),
    },
    ...allowedCategories.value.map((key) => ({
        key,
        icon: CATEGORY_ICONS[key],
        label: __(`statamic-emojis::messages.categories.${key}`),
    })),
]);

// Kept outside frimousse, so the search survives switching tabs (the picker remounts per tab)
const search = ref("");

// Data comes from the addon's CP route, filtered by category (see config/statamic-emojis.php)
const dataUrl = computed(() =>
    emojibaseUrl(
        tab.value === "search" ? allowedCategories.value : [tab.value],
    ),
);

// Runs synchronously, so frimousse's cache is cleared before <EmojiPicker.Root> (re)mounts
watch(
    [pickerLocale, dataUrl],
    ([locale, url]) => prepareFrimousseCache(locale, url),
    { immediate: true, flush: "sync" },
);

function onEmojiSelect({ emoji }) {
    emit("select", emoji);
}
</script>

<template>
    <div class="emoji-picker flex h-96 w-fit flex-col">
        <div
            class="flex shrink-0 items-center gap-0.5 border-b border-gray-200 p-2 dark:border-gray-700"
            role="tablist"
        >
            <Button
                v-for="item in tabs"
                :key="item.key"
                icon-only
                size="xs"
                :variant="tab === item.key ? 'pressed' : 'ghost'"
                role="tab"
                :aria-selected="tab === item.key"
                :aria-label="item.label"
                v-tooltip="item.label"
                @click="tab = item.key"
            >
                <component :is="item.icon" />
            </Button>
        </div>

        <!-- :key remounts the picker when the locale, tab or data changes, as frimousse only reads them on creation -->
        <EmojiPicker.Root
            :key="`${pickerLocale}|${dataUrl}`"
            class="flex min-h-0 flex-1 flex-col"
            :locale="pickerLocale"
            :columns="columns"
            :skin-tone="pickerSkinTone"
            :emojibase-url="dataUrl"
            :on-emoji-select="onEmojiSelect"
        >
            <EmojiPicker.Search
                v-if="tab === 'search'"
                class="mx-2 mt-2 block h-8 appearance-none rounded-md border border-gray-300 bg-white px-2.5 py-1.5 text-sm leading-[1.125rem] text-gray-925 antialiased shadow-ui-sm placeholder:text-gray-500 with-contrast:border-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:inset-shadow-2xs dark:inset-shadow-black dark:placeholder:text-gray-400/85 dark:with-contrast:border-gray-500"
                :placeholder="__('statamic-emojis::messages.search')"
                :value="search"
                @input="search = $event.target.value"
            />

            <EmojiPicker.Viewport class="relative mt-1 flex-1 outline-hidden">
                <EmojiPicker.Loading
                    class="absolute inset-0 flex items-center justify-center text-sm text-gray-500 dark:text-gray-400"
                >
                    {{ __("statamic-emojis::messages.loading") }}
                </EmojiPicker.Loading>
                <EmojiPicker.Empty
                    class="absolute inset-0 flex items-center justify-center text-sm text-gray-500 dark:text-gray-400"
                >
                    {{ __("statamic-emojis::messages.no_results") }}
                </EmojiPicker.Empty>
                <EmojiPicker.List class="select-none pb-1.5" />
            </EmojiPicker.Viewport>

            <div
                class="flex h-12 w-0 min-w-full shrink-0 items-center gap-2 border-t border-gray-200 px-3 dark:border-gray-700"
            >
                <EmojiPicker.ActiveEmoji v-slot="{ emoji }">
                    <template v-if="emoji">
                        <span
                            class="emoji-picker-preview shrink-0 text-3xl leading-none"
                            aria-hidden="true"
                            >{{ emoji.emoji }}</span
                        >
                        <span
                            class="min-w-0 flex-1 truncate text-sm text-gray-700 dark:text-gray-300"
                            :title="emoji.label"
                            >{{ emoji.label }}</span
                        >
                    </template>
                    <span
                        v-else
                        class="min-w-0 flex-1 truncate text-sm text-gray-500 dark:text-gray-400"
                    >
                        {{ __("statamic-emojis::messages.pick_an_emoji") }}
                    </span>
                </EmojiPicker.ActiveEmoji>
                <SkinToneButton
                    v-if="skinToneSelector"
                    class="ms-auto shrink-0"
                    size="sm"
                    :model-value="pickerSkinTone"
                    @update:model-value="onSkinToneChange"
                />
            </div>
        </EmojiPicker.Root>
    </div>
</template>

<style>
/* vue-frimousse exposes [frimousse-*] attributes on every part for styling.
   Colors use Statamic's UI tokens (--color-* -> --theme-color-*), so they follow the CP theme. */
.emoji-picker [frimousse-search]::-webkit-search-cancel-button {
    cursor: pointer;
}

.emoji-picker-preview {
    font-family: var(
        --frimousse-emoji-font,
        "Apple Color Emoji",
        "Segoe UI Emoji",
        "Noto Color Emoji",
        sans-serif
    );
}

.emoji-picker [frimousse-viewport] {
    /* Thin scrollbar like the CP's st-custom-scrollbar (gray thumb, transparent track so the popover background shows) */
    scrollbar-width: thin;
    scrollbar-color: var(--color-gray-400) transparent;
}

.emoji-picker [frimousse-category-header] {
    padding: 0.75rem 0.75rem 0.375rem;
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--color-gray-600);
    /* Sticky header needs an opaque background: same as the popover */
    background-color: var(--color-white);
}

.emoji-picker [frimousse-row] {
    padding-inline: 0.375rem;
    scroll-margin-block: 0.375rem;
}

.emoji-picker [frimousse-emoji] {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: var(--radius-md, 0.375rem);
    font-size: 1.125rem;
}

.emoji-picker [frimousse-emoji][data-active] {
    background-color: var(--color-gray-100);
}

.dark .emoji-picker [frimousse-viewport] {
    scrollbar-color: var(--color-gray-500) transparent;
}

.dark .emoji-picker [frimousse-category-header] {
    color: var(--color-gray-400);
    background-color: var(--color-gray-800);
}

.dark .emoji-picker [frimousse-emoji][data-active] {
    background-color: var(--color-gray-700);
}
</style>
