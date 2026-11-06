import EmojiFieldtype from './components/fieldtypes/EmojiFieldtype.vue';
import EmojiToolbarButton from './components/bard/EmojiToolbarButton.vue';

Statamic.booting(() => {
    // Fieldtype: PHP handle "emoji" maps to the Vue component "emoji-fieldtype"
    Statamic.component('emoji-fieldtype', EmojiFieldtype);

    // Bard: adds an "emoji" button that can be enabled in the Bard field's button config
    Statamic.$bard.buttons((buttons, button) => {
        return button({
            name: 'emoji',
            text: __('Emoji'),
            html: '🙂',
            component: EmojiToolbarButton,
        });
    });
});
