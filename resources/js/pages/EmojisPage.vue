<script setup>
import { onMounted, onUnmounted, ref, useTemplateRef } from "vue";
import { Alert, Button, Header, PublishContainer, PublishTabs } from "@statamic/cms/ui";
import { Pipeline, Request } from "@statamic/cms/save-pipeline";
import { router } from "@statamic/cms/inertia";

const props = defineProps({
    icon: { type: String, default: null },
    title: { type: String, required: true },
    blueprint: { type: Object, required: true },
    values: { type: Object, default: () => ({}) },
    meta: { type: Object, default: () => ({}) },
    submitUrl: { type: String, required: true },
    submitMethod: { type: String, default: "PATCH" },
    proUrl: { type: String, default: null },
});

const containerName = Statamic.$slug.separatedBy("_").create(props.title);
const container = useTemplateRef("container");
const values = ref(props.values);
const meta = ref(props.meta);
const errors = ref({});
const saving = ref(false);

function save() {
    new Pipeline()
        .provide({ container, errors, saving })
        .through([new Request(props.submitUrl, props.submitMethod)])
        .then((response) => {
            Statamic.$toast.success(__("Saved"));
            if (response.data.redirect) router.get(response.data.redirect);
        });
}

let saveKeyBinding;
onMounted(() => {
    saveKeyBinding = Statamic.$keys.bindGlobal(["mod+s"], (e) => {
        e.preventDefault();
        save();
    });
});
onUnmounted(() => saveKeyBinding?.destroy());
</script>

<template>
    <Header :title="title" :icon="icon">
        <Button variant="primary" :text="__('Save')" :disabled="saving" @click="save" />
    </Header>

    <Alert v-if="proUrl" variant="tip" class="mb-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="font-medium">{{ __("statamic-emojis::messages.settings.pro_alert_heading") }}</div>
                <div>{{ __("statamic-emojis::messages.settings.pro_alert_text") }}</div>
            </div>
            <Button
                variant="primary"
                size="sm"
                :href="proUrl"
                target="_blank"
                icon-append="external-link"
                :text="__('statamic-emojis::messages.settings.get_pro')"
            />
        </div>
    </Alert>

    <PublishContainer
        ref="container"
        v-model="values"
        :name="containerName"
        :blueprint="blueprint"
        :meta="meta"
        :errors="errors"
        as-config
    >
        <PublishTabs />
    </PublishContainer>
</template>
