import { reactive } from "vue";

const state = reactive({ list: [], loaded: false });
let request = null;

export const SHORTCODE = /^:([a-z0-9_+-]+):$/;

export function customEmojisEnabled() {
    return !!Statamic.$config.get("statamicEmojis")?.custom;
}

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

export function useCustomEmojis() {
    loadCustomEmojis();
    return state;
}

export function isShortcode(value) {
    return typeof value === "string" && SHORTCODE.test(value);
}

export function findCustom(code) {
    const shortcode = String(code ?? "").replace(/^:|:$/g, "");
    return state.list.find((emoji) => emoji.shortcode === shortcode) ?? null;
}

export function searchCustom(query) {
    const q = String(query ?? "")
        .trim()
        .toLowerCase();
    if (!q) return [];

    return state.list.filter((emoji) =>
        [emoji.shortcode, emoji.label, ...(emoji.keywords ?? [])].some((text) =>
            String(text).toLowerCase().includes(q),
        ),
    );
}
