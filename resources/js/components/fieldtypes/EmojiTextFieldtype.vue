<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, useTemplateRef } from "vue";
import { Fieldtype } from "@statamic/cms";
import { Button, Input, Popover, useContentDirection } from "@statamic/cms/ui";
import { SmilePlus } from "@lucide/vue";
import EmojiPicker from "../EmojiPicker.vue";
import EmojiSuggestions from "../EmojiSuggestions.vue";
import { pickerOptions, siteSettings } from "../../settings.js";
import { findTrigger, loadEmojiIndex, searchEmojis } from "../../emoji-search.js";
import { rememberEmoji } from "../../recent-emojis.js";

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { name, isReadOnly, update, updateDebounced, expose } = Fieldtype.use(emit, props);
defineExpose(expose);

const { direction: contentDirection } = useContentDirection();

// Same autofocus behaviour as the core Text fieldtype
const shouldFocus = computed(() => {
    if (props.config.focus === false || props.config.focus === true) {
        return props.config.focus;
    }

    const isRootField = !props.fieldPathPrefix;
    const isImplicitAutofocusField = name.value === "title" || name.value === "alt";

    return isRootField && isImplicitAutofocusField;
});

function inputUpdated(value) {
    return !props.config.debounce ? update(value) : updateDebounced(value);
}

const input = useTemplateRef("input");
// Until the input has been focused, its caret position is meaningless, so emojis are appended.
const hasCaret = ref(false);

function nativeInput() {
    return input.value?.$el?.querySelector("input") ?? null;
}

function onFocus() {
    hasCaret.value = true;
    emit("focus");
}

// The caret position survives the blur caused by opening the picker.
function insert(emoji) {
    const value = props.value ?? "";
    const el = nativeInput();
    const start = hasCaret.value && el ? (el.selectionStart ?? value.length) : value.length;
    const end = hasCaret.value && el ? (el.selectionEnd ?? start) : value.length;

    update(value.slice(0, start) + emoji + value.slice(end));

    // Put the caret right after the inserted emoji
    const caret = start + emoji.length;
    nextTick(() => {
        if (!el) return;
        el.focus();
        try {
            el.setSelectionRange(caret, caret);
        } catch {
            // Input types like email/number don't support selection ranges
        }
    });
}

// Shortcode suggestions: typing ":rock" lists matching emojis; ↑/↓ choose, Enter/Tab insert, Escape closes.
const suggest = reactive({ items: [], active: 0, start: 0, end: 0 });
let suggestRequest = 0;

function closeSuggestions() {
    suggestRequest++;
    suggest.items = [];
}

async function updateSuggestions() {
    const el = nativeInput();

    if (!el || isReadOnly.value || siteSettings().suggestions === false || el.selectionStart !== el.selectionEnd) {
        return closeSuggestions();
    }

    const cursor = el.selectionStart ?? 0;
    const trigger = findTrigger(el.value.slice(0, cursor));
    if (!trigger) return closeSuggestions();

    const request = ++suggestRequest;
    await loadEmojiIndex();
    if (request !== suggestRequest) return;

    Object.assign(suggest, {
        items: searchEmojis(trigger.query, { customEmojis: pickerOptions(props.config).customEmojis }),
        active: 0,
        start: cursor - trigger.length,
        end: cursor,
    });
}

function onSuggestKeydown(event) {
    const count = suggest.items.length;
    if (!count) return;

    if (event.key === "ArrowDown") suggest.active = (suggest.active + 1) % count;
    else if (event.key === "ArrowUp") suggest.active = (suggest.active - 1 + count) % count;
    else if (event.key === "Enter" || event.key === "Tab") pickSuggestion(suggest.items[suggest.active]);
    else if (event.key === "Escape") {
        closeSuggestions();
        event.stopPropagation(); // don't close a surrounding stack or modal
    } else return;

    event.preventDefault();
}

function onSuggestKeyup(event) {
    if (["ArrowLeft", "ArrowRight", "Home", "End"].includes(event.key)) updateSuggestions();
}

function pickSuggestion(item) {
    const el = nativeInput();
    const value = el?.value ?? props.value ?? "";
    const caret = suggest.start + item.emoji.length;

    update(value.slice(0, suggest.start) + item.emoji + value.slice(suggest.end));
    rememberEmoji(item.emoji, item.label);
    closeSuggestions();

    nextTick(() => {
        if (!el) return;
        el.focus();
        try {
            el.setSelectionRange(caret, caret);
        } catch {
            // Input types like email/number don't support selection ranges
        }
    });
}

const suggestListeners = {
    input: updateSuggestions,
    click: updateSuggestions,
    keydown: onSuggestKeydown,
    keyup: onSuggestKeyup,
    blur: closeSuggestions,
};

onMounted(() => {
    const el = nativeInput();
    Object.entries(suggestListeners).forEach(([event, listener]) => el?.addEventListener(event, listener));
});

onBeforeUnmount(() => {
    const el = nativeInput();
    Object.entries(suggestListeners).forEach(([event, listener]) => el?.removeEventListener(event, listener));
});
</script>

<template>
    <div class="relative flex items-center gap-2">
        <!-- Same config pass-through as the core Text fieldtype.
             Input doesn't emit focus/blur and those events don't bubble to its wrapper, so use focusin/focusout. -->
        <Input
            ref="input"
            class="flex-1"
            :model-value="value"
            :classes="config.classes"
            :focus="shouldFocus"
            :autocomplete="config.autocomplete"
            :autoselect="config.autoselect"
            :type="config.input_type"
            :read-only="isReadOnly"
            :prepend="__(config.prepend)"
            :append="__(config.append)"
            :limit="config.character_limit"
            :placeholder="__(config.placeholder)"
            :name="name"
            :id="id"
            :input-attrs="{ dir: contentDirection }"
            @update:model-value="inputUpdated"
            @focusin="onFocus"
            @focusout="$emit('blur')"
        />
        <Popover
            v-if="!isReadOnly"
            inset
            class="w-fit! overflow-hidden"
            align="end"
        >
            <template #trigger>
                <!-- Statamic's icon set has no smiley, so use Lucide's -->
                <Button
                    icon-only
                    :aria-label="__('statamic-emojis::messages.insert_emoji')"
                    v-tooltip="__('statamic-emojis::messages.insert_emoji')"
                >
                    <SmilePlus class="size-4" />
                </Button>
            </template>
            <EmojiPicker
                v-bind="pickerOptions(config)"
                @select="insert"
            />
        </Popover>
        <EmojiSuggestions
            v-if="suggest.items.length"
            class="absolute top-full left-0 z-50 mt-1"
            :items="suggest.items"
            :active="suggest.active"
            :set="config.emoji_set"
            @select="pickSuggestion"
            @hover="suggest.active = $event"
        />
    </div>
</template>
