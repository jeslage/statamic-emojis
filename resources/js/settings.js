export function siteSettings() {
    return Statamic.$config.get("statamicEmojis") ?? {};
}

function showHide(value, fallback) {
    if (value === true || value === "show") return true;
    if (value === false || value === "hide") return false;
    return fallback;
}

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
