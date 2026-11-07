import EmojiFieldtype from "./components/fieldtypes/EmojiFieldtype.vue";
import EmojiTextFieldtype from "./components/fieldtypes/EmojiTextFieldtype.vue";
import EmojiIndexFieldtype from "./components/fieldtypes/EmojiIndexFieldtype.vue";
import EmojiTextIndexFieldtype from "./components/fieldtypes/EmojiTextIndexFieldtype.vue";
import CustomEmojiGridFieldtype from "./components/fieldtypes/CustomEmojiGridFieldtype.vue";
import ImageCacheFieldtype from "./components/fieldtypes/ImageCacheFieldtype.vue";
import SkinToneFieldtype from "./components/fieldtypes/SkinToneFieldtype.vue";
import EmojiToolbarButton from "./components/bard/EmojiToolbarButton.vue";
import customEmojiNode from "./bard/custom-emoji-node.js";
import emojiSuggestions from "./bard/emoji-suggestions.js";
import emojiSetImages from "./bard/emoji-set-images.js";
import { h, render } from "vue";
import { SmilePlus } from "@lucide/vue";

// Bard's button configurator (blueprint editor) shows a button's `html`, so render the icon to markup.
function iconHtml(icon) {
    const el = document.createElement("div");
    render(h(icon, { size: 16 }), el);
    return el.innerHTML;
}

Statamic.booting(() => {
    // Fieldtypes: Statamic resolves a PHP handle to the Vue component "{handle}-fieldtype"
    Statamic.component("emoji-fieldtype", EmojiFieldtype);
    Statamic.component("emoji_text-fieldtype", EmojiTextFieldtype);

    // Entry listing columns: emojis as they look in the fields (custom images, site-wide set)
    Statamic.component("emoji-fieldtype-index", EmojiIndexFieldtype);
    Statamic.component("emoji_text-fieldtype-index", EmojiTextIndexFieldtype);

    // Display-only fields of the Emojis page
    Statamic.component("statamic_emojis_custom_grid-fieldtype", CustomEmojiGridFieldtype);
    Statamic.component("statamic_emojis_image_cache-fieldtype", ImageCacheFieldtype);

    // Settings: skin tone as 👋 in each tone
    Statamic.component("statamic_emojis_skin_tone-fieldtype", SkinToneFieldtype);

    // Bard: inline node for custom emojis (":shortcode:"), rendered as their image in the editor
    Statamic.$bard.addExtension(customEmojiNode);

    // Bard: Unicode emojis shown in the site-wide emoji set
    Statamic.$bard.addExtension(emojiSetImages);

    // Bard: shortcode suggestions (":rock") in fields with the emoji button
    Statamic.$bard.addExtension(emojiSuggestions);

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
