// Site-wide settings from the Emojis CP page (provided by the ServiceProvider as Statamic.$config 'statamicEmojis')
// merged with a field's own "Emoji Picker" settings: empty field values use the site-wide ones.

export function siteSettings() {
    return Statamic.$config.get("statamicEmojis") ?? {};
}

/** "show"/"hide" (or legacy booleans) from a field, else the site-wide default. */
function showHide(value, fallback) {
    if (value === true || value === "show") return true;
    if (value === false || value === "hide") return false;
    return fallback;
}

/** Props for <EmojiPicker> from a field's config (empty config = site-wide settings, e.g. the Bard button). */
export function pickerOptions(config = {}) {
    const site = siteSettings();

    return {
        categories: config.categories?.length ? config.categories : (site.categories ?? []),
        skinTone: config.skin_tone || site.skinTone || "none",
        skinToneSelector: showHide(config.skin_tone_selector, site.skinToneSelector ?? true),
        customEmojis: showHide(config.custom_emojis, true) && !!site.custom,
        recentEmojis: site.recent ?? true,
    };
}
