<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, useTemplateRef } from "vue";
import { Fieldtype } from "@statamic/cms";
import { useContentDirection } from "@statamic/cms/ui";
import EmojiPickerButton from "../EmojiPickerButton.vue";
import EmojiSuggestions from "../EmojiSuggestions.vue";
import EmojiTextInput from "../EmojiTextInput.vue";
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

/** The editor (EmojiTextInput: shows custom emojis as images); offsets are positions in the value. */
function field() {
    const editor = input.value;
    if (!editor?.el) return null;
    return {
        el: editor.el,
        text: () => editor.text(),
        selection: () => editor.selection(),
        focus: () => editor.focus(),
        setCaret: (offset) => editor.setSelection(offset),
    };
}

function focusAt(target, offset) {
    nextTick(() => {
        if (!target) return;
        target.focus();
        target.setCaret(offset);
    });
}

function onFocus() {
    hasCaret.value = true;
    emit("focus");
}

// The caret position survives the blur caused by opening the picker.
function insert(emoji) {
    const target = field();
    const value = target?.text() ?? props.value ?? "";
    const selection = hasCaret.value ? target?.selection() : null;
    const start = Math.min(selection?.start ?? value.length, value.length);
    const end = Math.min(selection?.end ?? start, value.length);

    update(value.slice(0, start) + emoji + value.slice(end));

    // Put the caret right after the inserted emoji
    focusAt(target, start + emoji.length);
}

// Shortcode suggestions: typing ":rock" lists matching emojis; ↑/↓ choose, Enter/Tab insert, Escape closes.
// The list is teleported to <body> (so following sections can't cover it) and fixed below the input.
const suggest = reactive({ items: [], active: 0, start: 0, end: 0, top: 0, left: 0 });
let suggestRequest = 0;

function positionSuggestions() {
    const rect = input.value?.$el?.getBoundingClientRect();
    if (!rect) return;
    suggest.top = rect.bottom + 4;
    suggest.left = rect.left;
}

function watchPosition(on) {
    const method = on ? "addEventListener" : "removeEventListener";
    window[method]("scroll", positionSuggestions, true);
    window[method]("resize", positionSuggestions);
}

function closeSuggestions() {
    suggestRequest++;
    suggest.items = [];
    watchPosition(false);
}

async function updateSuggestions() {
    const target = field();
    const selection = target?.selection();

    if (!selection || isReadOnly.value || siteSettings().suggestions === false || selection.start !== selection.end) {
        return closeSuggestions();
    }

    const cursor = selection.start ?? 0;
    const trigger = findTrigger(target.text().slice(0, cursor));
    if (!trigger) return closeSuggestions();

    const request = ++suggestRequest;
    await loadEmojiIndex();
    if (request !== suggestRequest) return;

    const items = searchEmojis(trigger.query, { customEmojis: pickerOptions(props.config).customEmojis });
    if (!items.length) return closeSuggestions();

    Object.assign(suggest, { items, active: 0, start: cursor - trigger.length, end: cursor });
    positionSuggestions();
    watchPosition(true);
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
    const target = field();
    const value = target?.text() ?? props.value ?? "";

    update(value.slice(0, suggest.start) + item.emoji + value.slice(suggest.end));
    rememberEmoji(item.emoji, item.label);
    closeSuggestions();
    focusAt(target, suggest.start + item.emoji.length);
}

const suggestListeners = {
    input: updateSuggestions,
    click: updateSuggestions,
    keydown: onSuggestKeydown,
    keyup: onSuggestKeyup,
    blur: closeSuggestions,
};

let listenedEl = null;

onMounted(() => {
    listenedEl = field()?.el ?? null;
    Object.entries(suggestListeners).forEach(([event, listener]) => listenedEl?.addEventListener(event, listener));
});

onBeforeUnmount(() => {
    Object.entries(suggestListeners).forEach(([event, listener]) => listenedEl?.removeEventListener(event, listener));
    watchPosition(false);
});
</script>

<template>
    <div>
        <!-- Same config as the core Text fieldtype (without input type and autocomplete).
             focusin/focusout, as focus and blur don't bubble from the editable element. -->
        <EmojiTextInput
            ref="input"
            :model-value="value"
            :focus="shouldFocus"
            :autoselect="config.autoselect"
            :read-only="isReadOnly"
            :prepend="__(config.prepend)"
            :append="__(config.append)"
            :limit="config.character_limit"
            :placeholder="__(config.placeholder)"
            :id="id"
            :dir="contentDirection"
            @update:model-value="inputUpdated"
            @focusin="onFocus"
            @focusout="$emit('blur')"
        >
            <template v-if="!isReadOnly" #end>
                <EmojiPickerButton inset :options="pickerOptions(config)" @select="insert" />
            </template>
        </EmojiTextInput>
        <Teleport to="body">
            <EmojiSuggestions
                v-if="suggest.items.length"
                :style="{ position: 'fixed', zIndex: 9999, top: `${suggest.top}px`, left: `${suggest.left}px` }"
                :items="suggest.items"
                :active="suggest.active"
                @select="pickSuggestion"
                @hover="suggest.active = $event"
            />
        </Teleport>
    </div>
</template>
