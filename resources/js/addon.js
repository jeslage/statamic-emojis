import EmojiFieldtype from "./components/fieldtypes/EmojiFieldtype.vue";
import EmojiToolbarButton from "./components/bard/EmojiToolbarButton.vue";
import { h, render } from "vue";
import { SmilePlus } from "@lucide/vue";

// Bard's button configurator (blueprint editor) shows a button's `html`, so render the icon to markup.
function iconHtml(icon) {
    const el = document.createElement("div");
    render(h(icon, { size: 16 }), el);
    return el.innerHTML;
}

Statamic.booting(() => {
    // Fieldtype: PHP handle "emoji" maps to the Vue component "emoji-fieldtype"
    Statamic.component("emoji-fieldtype", EmojiFieldtype);

    // Bard: adds an "emoji" button that can be enabled in the Bard field's button config
    Statamic.$bard.buttons((buttons, button) => {
        return button({
            name: "emoji",
            text: __("statamic-emojis::messages.emoji"),
            html: iconHtml(SmilePlus),
            component: EmojiToolbarButton,
        });
    });
});
