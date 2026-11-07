<script setup>
// Shared emoji picker used by both the fieldtype and the Bard toolbar button.
// Built on vue-frimousse (https://vue-frimousse.robertshaw.id), which is unstyled and composable.
import { computed, onMounted, ref, useTemplateRef, watch } from "vue";
import EmojiPicker from "vue-frimousse";
import { Button } from "@statamic/cms/ui";
import {
    Flag,
    Lightbulb,
    Plane,
    Hamburger,
    Search,
    Smile,
    RugbyBall,
    Pilcrow,
    Leaf,
    Sparkles,
} from "@lucide/vue";
import { cpEmojibaseLocale, emojibaseLocale } from "../locale.js";
import {
    CATEGORIES,
    applyRecentToFrimousseCache,
    emojibaseUrl,
    prepareFrimousseCache,
} from "../emojibase.js";
import EmojiImage from "./EmojiImage.vue";
import PickerFooter from "./PickerFooter.vue";
import EmojiGrid from "./EmojiGrid.vue";
import { findCustom, isShortcode, searchCustom, useCustomEmojis } from "../custom-emojis.js";
import { rememberEmoji, useRecentEmojis } from "../recent-emojis.js";

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
    "smileys-people": Smile,
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
    // Offer custom emojis (field config `custom_emojis`)
    customEmojis: { type: Boolean, default: true },
    // Recently used emojis above the list in the search tab (site-wide setting); not with a category limit
    recentEmojis: { type: Boolean, default: true },
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

// Custom emojis: own tab (if there are any) and matches above the search results
const custom = useCustomEmojis();
const customList = computed(() => (props.customEmojis ? custom.list : []));
const asItem = (emoji) => ({ emoji: `:${emoji.shortcode}:`, label: emoji.label });

// Recent emojis (per browser). Hidden with a category limit, as their categories aren't known.
// Custom emojis in the list are dropped if they're disabled for this field or were deleted.
const recent = useRecentEmojis();
const recentItems = computed(() =>
    recent.value.filter((item) =>
        // Custom emojis: only if allowed here and (once the list is loaded) not deleted
        isShortcode(item.emoji) ? props.customEmojis && (!custom.loaded || findCustom(item.emoji)) : true,
    ),
);
// First category of the search tab's list, at most two rows
const recentRow = computed(() =>
    props.recentEmojis && !props.categories?.length ? recentItems.value.slice(0, props.columns * 2) : [],
);

// Item hovered in one of our own grids (custom tab, custom matches); frimousse's own hover is handled by ActiveEmoji
const hovered = ref(null);

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
    ...(customList.value.length
        ? [{ key: "custom", icon: Sparkles, label: __("statamic-emojis::messages.custom.tab") }]
        : []),
]);

// Kept outside frimousse, so the search survives switching tabs (the picker remounts per tab)
const search = ref("");

// Custom emojis matching the search; ones in "Recently used" already show up there (frimousse searches it too)
const customMatches = computed(() => {
    if (!props.customEmojis) return [];
    const inRecent = new Set(recentRow.value.map((item) => item.emoji));
    return searchCustom(search.value).map(asItem).filter((item) => !inRecent.has(item.emoji));
});

// Tabs rendered by our own grid instead of frimousse
const gridItems = computed(() =>
    tab.value === "custom" ? customList.value.map(asItem) : null,
);

// Data comes from the addon's CP route, filtered by category (see config/statamic-emojis.php)
const dataUrl = computed(() =>
    emojibaseUrl(
        // The custom tab doesn't use frimousse; keep the search tab's data so its cache stays put
        ["search", "custom"].includes(tab.value) ? allowedCategories.value : [tab.value],
    ),
);

// Runs synchronously, so frimousse's cache is prepared before <EmojiPicker.Root> (re)mounts:
// the data of this URL, plus the "Recently used" category in the search tab
watch(
    [pickerLocale, dataUrl, tab],
    ([locale, url, currentTab]) => {
        prepareFrimousseCache(locale, url);
        applyRecentToFrimousseCache(
            locale,
            currentTab === "search" ? recentRow.value : [],
            __("statamic-emojis::messages.recent_tab"),
        );
    },
    { immediate: true, flush: "sync" },
);

// Focus the search when the picker opens. The popover focuses its content first, so wait a tick.
const pickerRoot = useTemplateRef("pickerRoot");

onMounted(() => {
    setTimeout(() => pickerRoot.value?.querySelector("[frimousse-search]")?.focus({ preventScroll: true }), 0);
});

function select(emoji, label) {
    hovered.value = null;
    rememberEmoji(emoji, label);
    emit("select", emoji);
}

function onEmojiSelect({ emoji, label }) {
    select(emoji, label);
}
</script>

<template>
    <div ref="pickerRoot" class="emoji-picker flex h-96 w-fit flex-col">
        <!-- w-0 min-w-full: the tab bar never widens the picker; if the tabs don't fit, it scrolls sideways -->
        <div
            class="emoji-picker-tabs flex w-0 min-w-full shrink-0 items-center overflow-x-auto border-b border-gray-200 p-2 dark:border-gray-700"
            role="tablist"
        >
            <Button
                v-for="item in tabs"
                :key="item.key"
                class="shrink-0"
                icon-only
                :size="tabs.length > 9 ? 'xs' : 'sm'"
                :variant="tab === item.key ? 'default' : 'subtle'"
                role="tab"
                :aria-selected="tab === item.key"
                :aria-label="item.label"
                v-tooltip="item.label"
                @click="tab = item.key"
            >
                <component :is="item.icon" />
            </Button>
        </div>

        <!-- Custom tab: our own grid -->
        <div v-if="gridItems" class="flex min-h-0 flex-1 flex-col">
            <div class="st-custom-scrollbar min-h-0 flex-1 overflow-y-auto pt-2 pb-1.5">
                <EmojiGrid
                    :items="gridItems"
                    :columns="columns"
                    @select="select($event.emoji, $event.label)"
                    @hover="hovered = $event"
                />
            </div>
            <PickerFooter
                :emoji="hovered?.emoji"
                :label="hovered?.label"
                :skin-tone-selector="skinToneSelector"
                :skin-tone="pickerSkinTone"
                @update:skin-tone="onSkinToneChange"
            />
        </div>

        <!-- :key remounts the picker when the locale, tab or data changes, as frimousse only reads them on creation -->
        <EmojiPicker.Root
            v-else
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

            <!-- Custom emojis matching the search, above the regular results -->
            <div v-if="tab === 'search' && customMatches.length" class="mt-2 shrink-0">
                <div class="custom-emoji-header">{{ __("statamic-emojis::messages.custom.tab") }}</div>
                <EmojiGrid
                    :items="customMatches"
                    :columns="columns"
                    @select="select($event.emoji, $event.label)"
                    @hover="hovered = $event"
                />
            </div>

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
                <!-- Only the cell's content is replaced; frimousse keeps click, hover and keyboard handling -->
                <EmojiPicker.List class="select-none pb-1.5">
                    <template #emoji="{ emoji }">
                        <EmojiImage :emoji="emoji.emoji" />
                    </template>
                </EmojiPicker.List>
            </EmojiPicker.Viewport>

            <EmojiPicker.ActiveEmoji v-slot="{ emoji }">
                <PickerFooter
                    :emoji="hovered ? hovered.emoji : emoji?.emoji"
                    :label="hovered ? hovered.label : emoji?.label"
                    :skin-tone-selector="skinToneSelector"
                    :skin-tone="pickerSkinTone"
                    @update:skin-tone="onSkinToneChange"
                />
            </EmojiPicker.ActiveEmoji>
        </EmojiPicker.Root>
    </div>
</template>

<style>
/* vue-frimousse exposes [frimousse-*] attributes on every part for styling.
   Colors use Statamic's UI tokens (--color-* -> --theme-color-*), so they follow the CP theme. */
.emoji-picker [frimousse-search]::-webkit-search-cancel-button {
    cursor: pointer;
}

.emoji-picker-tabs {
    /* Scrollable via trackpad, shift+wheel or keyboard focus; a scrollbar would crowd the tabs */
    scrollbar-width: none;
}

.emoji-picker-tabs::-webkit-scrollbar {
    display: none;
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

.emoji-picker [frimousse-viewport] > [aria-live] {
    /* vue-frimousse sets this screen reader announcer's size as unitless numbers (width: 1, height: 1,
       margin: -1), which Vue doesn't turn into px. The browser ignores them, so the announcer grows
       with the hovered emoji's name and makes the list scroll sideways. */
    width: 1px !important;
    height: 1px !important;
    margin: -1px !important;
}

.emoji-picker [frimousse-viewport] {
    scrollbar-width: thin;
    scrollbar-color: var(--color-gray-400) transparent;
    overflow-x: hidden;
}

.emoji-picker [frimousse-category-header] {
    padding: 0 0.75rem 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--color-gray-600);
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

.emoji-picker [frimousse-emoji] .emoji-image {
    /* Image sets: same visual size as the native characters */
    width: 1.375rem;
    height: 1.375rem;
}

.emoji-picker .custom-emoji-header {
    padding: 0 0.75rem 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--color-gray-600);
}

.emoji-picker button[frimousse-emoji] {
    cursor: pointer;
}

.emoji-picker button[frimousse-emoji]:hover,
.emoji-picker button[frimousse-emoji]:focus-visible {
    background-color: var(--color-gray-100);
    outline: none;
}

.dark .emoji-picker .custom-emoji-header {
    color: var(--color-gray-400);
}

.dark .emoji-picker button[frimousse-emoji]:hover,
.dark .emoji-picker button[frimousse-emoji]:focus-visible {
    background-color: var(--color-gray-700);
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
