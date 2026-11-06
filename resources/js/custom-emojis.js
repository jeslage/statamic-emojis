// Custom emojis (editor-uploaded images, referenced as ":shortcode:"), loaded once from the CP route
// /cp/statamic-emojis/custom and shared by all pickers, fields and Bard editors on the page.
import { reactive } from "vue";

const state = reactive({ list: [], loaded: false });
let request = null;

export const SHORTCODE = /^:([a-z0-9_+-]+):$/;

export function customEmojisEnabled() {
    return !!Statamic.$config.get("statamicEmojis")?.custom;
}

/** Loads the list once; resolves with the array of {shortcode, label, keywords, url}. */
export function loadCustomEmojis() {
    if (!customEmojisEnabled()) {
        state.loaded = true;
        return Promise.resolve([]);
    }

    request ??= fetch(`${Statamic.$config.get("cpUrl")}/statamic-emojis/custom`, {
        headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" },
        credentials: "same-origin",
    })
        .then((response) => (response.ok ? response.json() : []))
        .catch(() => [])
        .then((list) => {
            state.list = list;
            state.loaded = true;
            return list;
        });

    return request;
}

/** Reactive state: { list, loaded }. Starts loading on first use. */
export function useCustomEmojis() {
    loadCustomEmojis();
    return state;
}

export function isShortcode(value) {
    return typeof value === "string" && SHORTCODE.test(value);
}

/** The custom emoji for ":shortcode:" or "shortcode", or null. */
export function findCustom(code) {
    const shortcode = String(code ?? "").replace(/^:|:$/g, "");
    return state.list.find((emoji) => emoji.shortcode === shortcode) ?? null;
}

/** Custom emojis matching a search in shortcode, label or keywords. */
export function searchCustom(query) {
    const q = String(query ?? "").trim().toLowerCase();
    if (!q) return [];

    return state.list.filter((emoji) =>
        [emoji.shortcode, emoji.label, ...(emoji.keywords ?? [])].some((text) =>
            String(text).toLowerCase().includes(q),
        ),
    );
}
