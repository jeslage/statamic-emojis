// Emoji data is served by the addon's Control Panel route (routes/cp.php), filtered by category.
// vue-frimousse requests "{emojibaseUrl}/{locale}/data.json" and ".../messages.json".

/** Category keys in Emojibase order (group 2 "component" is never shown). Keep in sync with src/Emojibase.php. */
export const CATEGORIES = [
    "smileys-emotion",
    "people-body",
    "animals-nature",
    "food-drink",
    "travel-places",
    "activities",
    "objects",
    "symbols",
    "flags",
];

/**
 * Base URL for the picker's Emojibase data, limited to the given category keys (empty = all).
 */
export function emojibaseUrl(categories = []) {
    const key = categories?.length ? [...categories].sort().join(",") : "all";

    return `${Statamic.$config.get("cpUrl")}/statamic-emojis/emojibase/${key}`;
}

// Processed frimousse data per "locale|url", kept for this page view so switching tabs/fields is instant.
const stash = new Map();

/**
 * vue-frimousse caches its processed data in localStorage per locale only ("frimousse/data/{locale}"),
 * regardless of the URL it came from. Before a picker mounts with another data URL (other categories,
 * another tab), stash the cached data under its URL and restore the new URL's data, or drop it so
 * frimousse fetches it (the responses are HTTP-cached, so that stays cheap too).
 */
export function prepareFrimousseCache(locale, url) {
    try {
        const dataKey = `frimousse/data/${locale}`;
        const markerKey = `statamic-emojis/data-url/${locale}`;
        const currentUrl = localStorage.getItem(markerKey);

        if (currentUrl === url) return;

        const cached = localStorage.getItem(dataKey);
        if (currentUrl && cached) stash.set(`${locale}|${currentUrl}`, cached);

        const restored = stash.get(`${locale}|${url}`);
        if (restored) localStorage.setItem(dataKey, restored);
        else localStorage.removeItem(dataKey);

        localStorage.setItem(markerKey, url);
    } catch {
        // Storage unavailable: frimousse can't cache either, so there's nothing to prepare
    }
}
