// Emoji data is served by the addon's Control Panel route (routes/cp.php), filtered by category.
// vue-frimousse requests "{emojibaseUrl}/{locale}/data.json" and ".../messages.json".

/**
 * Picker categories in Emojibase order. "smileys-people" merges the "smileys & emotion" and
 * "people & body" groups (like Slack). Keep in sync with src/Emojibase.php.
 */
export const CATEGORIES = [
    "smileys-people",
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

/** Lowercase hex codepoints of an emoji joined by "-", fully qualified. Same as EmojiSets::codepoints() in PHP. */
export function codepoints(emoji) {
    return Array.from(emoji ?? "", (char) => char.codePointAt(0).toString(16)).join("-");
}

/** Site-wide emoji set and the image route, provided by the ServiceProvider. */
function setsConfig() {
    return Statamic.$config.get("statamicEmojis") ?? { set: "native", imageUrl: "" };
}

/** A field's set if given, otherwise the site-wide set. */
export function resolveSet(set) {
    return set || setsConfig().set || "native";
}

/** Image URL of an emoji in an image set, or null for native. */
export function emojiImageUrl(emoji, set) {
    const resolved = resolveSet(set);

    return resolved === "native" ? null : `${setsConfig().imageUrl}/${resolved}/${codepoints(emoji)}.svg`;
}

// Category index of the "Recently used" category injected into frimousse's data (real ones are >= 0)
const RECENT_CATEGORY = -1;

/**
 * Put the recently used emojis into frimousse's cached data as the first category, so they're part of
 * its scrollable, virtualized list (sticky header, keyboard navigation, footer preview).
 * Always removes a previously injected category first; pass no items to only remove it.
 * Takes effect when the picker mounts; on a picker's very first load (nothing cached yet) it shows
 * from the next opening on.
 */
export function applyRecentToFrimousseCache(locale, items, label) {
    try {
        const key = `frimousse/data/${locale}`;
        const raw = localStorage.getItem(key);
        if (!raw) return;

        const cache = JSON.parse(raw);
        const data = cache?.data;
        if (!Array.isArray(data?.emojis) || !Array.isArray(data?.categories)) return;

        data.emojis = data.emojis.filter((emoji) => emoji.category !== RECENT_CATEGORY);
        data.categories = data.categories.filter((category) => category.index !== RECENT_CATEGORY);

        if (items.length) {
            data.categories.unshift({ index: RECENT_CATEGORY, label });
            data.emojis.unshift(
                // version 0: always supported; no skins: the stored emoji already has its skin tone
                ...items.map((item) => ({ emoji: item.emoji, category: RECENT_CATEGORY, label: item.label, version: 0, tags: [] })),
            );
        }

        localStorage.setItem(key, JSON.stringify(cache));
    } catch {
        // Storage unavailable or unexpected data: the picker works without the recent category
    }
}
