import EmojiFieldtype from "./components/fieldtypes/EmojiFieldtype.vue";
import EmojiTextFieldtype from "./components/fieldtypes/EmojiTextFieldtype.vue";
import EmojiIndexFieldtype from "./components/fieldtypes/EmojiIndexFieldtype.vue";
import EmojiTextIndexFieldtype from "./components/fieldtypes/EmojiTextIndexFieldtype.vue";
import CustomEmojiGridFieldtype from "./components/fieldtypes/CustomEmojiGridFieldtype.vue";
import ImageCacheFieldtype from "./components/fieldtypes/ImageCacheFieldtype.vue";
import EmojisPage from "./pages/EmojisPage.vue";
import EmojiSetFieldtype from "./components/fieldtypes/EmojiSetFieldtype.vue";
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
    Statamic.$inertia.register("statamic-emojis::EmojisPage", EmojisPage);

    Statamic.component("emoji-fieldtype", EmojiFieldtype);
    Statamic.component("emoji_text-fieldtype", EmojiTextFieldtype);

    Statamic.component("emoji-fieldtype-index", EmojiIndexFieldtype);
    Statamic.component("emoji_text-fieldtype-index", EmojiTextIndexFieldtype);

    Statamic.component("statamic_emojis_custom_grid-fieldtype", CustomEmojiGridFieldtype);
    Statamic.component("statamic_emojis_image_cache-fieldtype", ImageCacheFieldtype);

    Statamic.component("statamic_emojis_set-fieldtype", EmojiSetFieldtype);
    Statamic.component("statamic_emojis_skin_tone-fieldtype", SkinToneFieldtype);

    Statamic.$bard.addExtension(customEmojiNode);

    Statamic.$bard.addExtension(emojiSetImages);

    Statamic.$bard.addExtension(emojiSuggestions);

    Statamic.$bard.buttons((buttons, button) => {
        return button({
            name: "emoji",
            text: __("statamic-emojis::messages.emoji"),
            html: iconHtml(SmilePlus),
            component: EmojiToolbarButton,
        });
    });
});
