<script setup>
// Shared emoji picker used by both the fieldtype and the Bard toolbar button.
// TODO: choose and wire up a picker (e.g. emoji-mart, vue3-emoji-picker or a custom grid).

const emit = defineEmits(['select']);

function select(emoji) {
    emit('select', emoji);
}
</script>

<template>
    <div class="emoji-picker p-2">
        <!-- TODO: picker UI. Placeholder: -->
        <button type="button" @click="select('🙂')">🙂</button>
    </div>
</template>
