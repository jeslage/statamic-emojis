<script setup>
// Single-line text input that shows custom emojis (":shortcode:") as their images, and Unicode emojis
// as images of the site-wide emoji set (native characters for the native set).
// A native <input> can only show text, so this is a contenteditable element styled like Statamic's Input.
// The value stays a plain string: images are serialized back to ":shortcode:" and caret positions are
// offsets in that string, so callers can work with it like with an input's value and selectionStart.
import { nextTick, onBeforeUnmount, onMounted, ref, useSlots, useTemplateRef, watch } from "vue";
import { CharacterCounter, InputGroup, InputGroupAppend, InputGroupPrepend } from "@statamic/cms/ui";
import { findCustom, useCustomEmojis } from "../custom-emojis.js";
import { EMOJI_PATTERN, emojiImageUrl } from "../emojibase.js";

defineOptions({ inheritAttrs: false });

const props = defineProps({
    modelValue: { type: String, default: null },
    placeholder: { type: String, default: null },
    readOnly: { type: Boolean, default: false },
    limit: { type: Number, default: null },
    prepend: { type: String, default: null },
    append: { type: String, default: null },
    id: { type: String, default: null },
    dir: { type: String, default: null },
    focus: { type: Boolean, default: false },
    autoselect: { type: Boolean, default: false },
});

const emit = defineEmits(["update:modelValue"]);

const root = useTemplateRef("root");
// Content of the "end" slot (e.g. the picker button) sits inside the input at its end
const hasEnd = !!useSlots().end;
const customEmojis = useCustomEmojis();
const SHORTCODE = /:([a-z0-9_+-]+):/gi;

// Values this input emitted itself. With a debounced field the model lags behind the typing,
// and re-rendering an older emitted value would undo what was typed since.
const emitted = new Set();
// The selection survives the blur caused by opening the picker
let lastSelection = null;
let composing = false;
// Emojis the set has no image for: shown as native characters
const missing = new Set();

/** Text with Unicode emojis split out as images of the set: [{ text } | { emoji, url }] */
function parseUnicode(text) {
    if (!emojiImageUrl("😀")) return [{ text }]; // native set

    const parts = [];
    let last = 0;
    for (const match of text.matchAll(EMOJI_PATTERN)) {
        if (missing.has(match[0])) continue;
        if (match.index > last) parts.push({ text: text.slice(last, match.index) });
        parts.push({ emoji: match[0], url: emojiImageUrl(match[0]) });
        last = match.index + match[0].length;
    }
    if (last < text.length) parts.push({ text: text.slice(last) });

    return parts;
}

/** The value as text, custom emoji and Unicode emoji parts: [{ text } | { shortcode, url, label } | { emoji, url }] */
function parse(value) {
    return parseCustom(value).flatMap((part) => ("text" in part ? parseUnicode(part.text) : [part]));
}

function parseCustom(value) {
    const parts = [];
    let last = 0;
    let match;
    SHORTCODE.lastIndex = 0;

    while ((match = SHORTCODE.exec(value))) {
        const custom = findCustom(match[1]);
        if (!custom) {
            // ":a:approved:": the closing colon may start the next shortcode
            SHORTCODE.lastIndex = match.index + 1;
            continue;
        }
        if (match.index > last) parts.push({ text: value.slice(last, match.index) });
        parts.push({ shortcode: custom.shortcode, url: custom.url, label: custom.label });
        last = match.index + match[0].length;
    }
    if (last < value.length) parts.push({ text: value.slice(last) });

    return parts;
}

function isEmoji(node) {
    return node.nodeName === "IMG" && !!(node.dataset.shortcode || node.dataset.emoji);
}

/** What an image stands for in the value */
function emojiText(node) {
    return node.dataset.shortcode ? `:${node.dataset.shortcode}:` : node.dataset.emoji;
}

/** Text of a node or fragment, images as ":shortcode:". Line breaks become spaces (single line). */
function serialize(node) {
    let out = "";
    for (const child of node.childNodes) {
        if (child.nodeType === Node.TEXT_NODE) out += child.data.replace(/\n/g, " ");
        else if (isEmoji(child)) out += emojiText(child);
        else if (child.nodeType === Node.ELEMENT_NODE && child.nodeName !== "BR") out += serialize(child);
    }
    return out;
}

/** Whether the element's children already are exactly what the value renders to (plain text nodes and images). */
function rendered(value) {
    const current = [];
    for (const child of root.value.childNodes) {
        if (child.nodeType === Node.TEXT_NODE) {
            if (child.data === "") continue;
            const prev = current.at(-1);
            if (prev && "text" in prev) prev.text += child.data;
            else current.push({ text: child.data });
        } else if (isEmoji(child)) current.push({ image: emojiText(child) });
        else return false;
    }

    const wanted = parse(value).map((part) =>
        "text" in part ? { text: part.text } : { image: part.emoji ?? `:${part.shortcode}:` },
    );
    return JSON.stringify(current) === JSON.stringify(wanted);
}

function render(value) {
    const nodes = parse(value).map((part) => {
        if ("text" in part) return document.createTextNode(part.text);

        const img = document.createElement("img");
        img.draggable = false;
        img.className = "emoji-text-input-emoji";

        if (part.emoji) {
            img.src = part.url;
            img.alt = part.emoji;
            img.dataset.emoji = part.emoji;
            // No image in the set: show the native character instead
            img.addEventListener("error", () => {
                missing.add(part.emoji);
                if (root.value) normalize(serialize(root.value));
            }, { once: true });
            return img;
        }

        img.src = part.url;
        img.alt = `:${part.shortcode}:`;
        img.title = part.label ? `${part.label} :${part.shortcode}:` : `:${part.shortcode}:`;
        img.dataset.shortcode = part.shortcode;
        return img;
    });

    root.value.replaceChildren(...nodes);
    empty.value = value === "";
}

/** Offset in the value of a DOM position inside the element. */
function offsetOf(container, offset) {
    const range = document.createRange();
    range.selectNodeContents(root.value);
    range.setEnd(container, offset);
    return serialize(range.cloneContents()).length;
}

/** The DOM position of an offset in the value; never inside an image's shortcode. */
function positionOf(offset) {
    const el = root.value;
    let rest = offset;

    for (let i = 0; i < el.childNodes.length; i++) {
        const child = el.childNodes[i];
        const length = child.nodeType === Node.TEXT_NODE ? child.data.length : serialize({ childNodes: [child] }).length;

        if (child.nodeType === Node.TEXT_NODE && rest <= length) return [child, rest];
        if (child.nodeType !== Node.TEXT_NODE && rest <= 0) return [el, i];
        rest -= length;
        if (rest < 0) return [el, i + 1];
    }

    return [el, el.childNodes.length];
}

function hasFocus() {
    return !!root.value && document.activeElement === root.value;
}

/** { start, end } as offsets in the value; the last selection while the element doesn't have focus. */
function selection() {
    const sel = window.getSelection();
    if (hasFocus() && sel?.rangeCount && root.value.contains(sel.anchorNode)) {
        const range = sel.getRangeAt(0);
        const start = offsetOf(range.startContainer, range.startOffset);
        const end = offsetOf(range.endContainer, range.endOffset);
        return { start, end };
    }

    return lastSelection;
}

function setSelection(start, end = start) {
    const range = document.createRange();
    range.setStart(...positionOf(start));
    range.setEnd(...positionOf(end));
    const sel = window.getSelection();
    sel.removeAllRanges();
    sel.addRange(range);
    lastSelection = { start, end };
    keepCaretVisible();
}

function keepCaretVisible() {
    const sel = window.getSelection();
    if (!sel?.rangeCount || !root.value) return;

    const caret = sel.getRangeAt(0).getBoundingClientRect();
    const box = root.value.getBoundingClientRect();
    if (!caret.width && !caret.height && !caret.left) return; // empty element: nothing to scroll

    if (caret.right > box.right - 12) root.value.scrollLeft += caret.right - box.right + 12;
    else if (caret.left < box.left + 12) root.value.scrollLeft -= box.left + 12 - caret.left;
}

/** Re-renders when the DOM differs from what the value should look like (e.g. a shortcode was completed). */
function normalize(value) {
    if (rendered(value)) return;

    const sel = hasFocus() ? selection() : null;
    render(value);
    if (sel) setSelection(Math.min(sel.start, value.length), Math.min(sel.end, value.length));
}

function onInput() {
    if (composing) return;

    const value = serialize(root.value);
    normalize(value);
    empty.value = value === "";

    if (emitted.size > 50) emitted.clear();
    emitted.add(value);
    emit("update:modelValue", value);
}

function onCompositionEnd() {
    composing = false;
    onInput();
}

// Single line: no paragraphs or line breaks
function onBeforeInput(event) {
    if (["insertParagraph", "insertLineBreak"].includes(event.inputType)) event.preventDefault();
}

function onKeydown(event) {
    if (event.key === "Enter") event.preventDefault();
}

// Paste and drop plain text only; shortcodes in it become images on the following input event
function insertPlainText(text) {
    document.execCommand("insertText", false, text.replace(/\r?\n/g, " "));
}

function onPaste(event) {
    event.preventDefault();
    insertPlainText(event.clipboardData?.getData("text/plain") ?? "");
}

function onDrop(event) {
    event.preventDefault();
}

// Copy and cut the text with ":shortcode:" for images
function onCopy(event, cut = false) {
    const sel = window.getSelection();
    if (!sel?.rangeCount) return;

    event.preventDefault();
    event.clipboardData.setData("text/plain", serialize(sel.getRangeAt(0).cloneContents()));
    if (cut && !props.readOnly) document.execCommand("delete");
}

function onSelectionChange() {
    if (hasFocus()) lastSelection = selection();
}

function onFocus() {
    if (props.autoselect) nextTick(() => setSelection(0, serialize(root.value).length));
}

function focus() {
    root.value?.focus();
}

watch(
    () => props.modelValue,
    (value) => {
        value ??= "";
        if (!root.value || emitted.has(value) || value === serialize(root.value)) return;

        emitted.clear();
        normalize(value);
    },
);

// Shortcodes become images once the custom emojis are loaded
watch(
    () => customEmojis.list,
    () => root.value && normalize(serialize(root.value)),
);

onMounted(() => {
    render(props.modelValue ?? "");
    document.addEventListener("selectionchange", onSelectionChange);
    if (props.focus) nextTick(focus);
});

onBeforeUnmount(() => document.removeEventListener("selectionchange", onSelectionChange));

const empty = ref(!props.modelValue);

defineExpose({
    el: root,
    focus,
    selection,
    setSelection,
    text: () => (root.value ? serialize(root.value) : (props.modelValue ?? "")),
});
</script>

<template>
    <InputGroup v-bind="$attrs">
        <InputGroupPrepend v-if="prepend" v-text="prepend" />
        <div class="group/input relative block w-full st-text-legibility" data-ui-input>
            <div
                ref="root"
                :id="id"
                :dir="dir"
                role="textbox"
                aria-multiline="false"
                :aria-placeholder="placeholder || undefined"
                :aria-readonly="readOnly || undefined"
                :contenteditable="readOnly ? 'false' : 'true'"
                :tabindex="readOnly ? 0 : undefined"
                spellcheck="true"
                class="emoji-text-input not-prose block h-10 w-full appearance-none overflow-x-auto overflow-y-hidden rounded-lg border border-gray-300 bg-white px-3 py-2 text-base leading-[1.375rem] whitespace-pre text-gray-925 antialiased shadow-ui-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:inset-shadow-2xs dark:inset-shadow-black"
                :class="{
                    'pe-9': limit && !hasEnd,
                    'pe-10': hasEnd && !limit,
                    'pe-[4.5rem]': hasEnd && limit,
                    'border-dashed bg-gray-100 text-gray-600 shadow-none dark:text-gray-500': readOnly,
                }"
                data-ui-control
                data-ui-group-target
                @input="onInput"
                @beforeinput="onBeforeInput"
                @keydown="onKeydown"
                @paste="onPaste"
                @drop="onDrop"
                @copy="onCopy($event)"
                @cut="onCopy($event, true)"
                @focus="onFocus"
                @compositionstart="composing = true"
                @compositionend="onCompositionEnd"
            />
            <div
                v-if="empty && placeholder"
                class="pointer-events-none absolute inset-y-0 start-0 flex items-center truncate border border-transparent px-3 text-base text-gray-500 dark:text-gray-400/85"
                aria-hidden="true"
                v-text="placeholder"
            />
            <div v-if="limit" class="absolute inset-y-0 flex items-center" :class="hasEnd ? 'end-10' : 'end-2'">
                <CharacterCounter :text="modelValue ?? ''" :limit="limit" />
            </div>
            <div v-if="hasEnd" class="absolute inset-y-0 end-0 flex items-center pe-1">
                <slot name="end" />
            </div>
        </div>
        <InputGroupAppend v-if="append" v-text="append" />
    </InputGroup>
</template>
