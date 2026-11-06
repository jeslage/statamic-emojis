<script setup>
import { computed, nextTick, ref, useTemplateRef } from "vue";
import { Fieldtype } from "@statamic/cms";
import { Button, Input, Popover, useContentDirection } from "@statamic/cms/ui";
import { SmilePlus } from "@lucide/vue";
import EmojiPicker from "../EmojiPicker.vue";

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
</script>

<template>
    <div class="flex items-center gap-2">
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
                :categories="config.categories ?? []"
                :skin-tone="config.skin_tone ?? 'none'"
                :skin-tone-selector="config.skin_tone_selector ?? true"
                @select="insert"
            />
        </Popover>
    </div>
</template>
