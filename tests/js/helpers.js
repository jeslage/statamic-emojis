import { vi } from "vitest";

export function mockFetch(routes) {
    const fetch = vi.fn(async (url) => {
        const data = routes[url];
        return data === undefined
            ? { ok: false, status: 404, json: async () => null }
            : { ok: true, status: 200, json: async () => data };
    });
    vi.stubGlobal("fetch", fetch);
    return fetch;
}

export const CUSTOM_URL = "/cp/statamic-emojis/custom";
export const DATA_URL = "/cp/statamic-emojis/emojibase/all/en/data.json";
export const SHORTCODES_URL = "/cp/statamic-emojis/emojibase/shortcodes.json";

export const CUSTOM_EMOJIS = [
    { shortcode: "approved", label: "Approved", keywords: ["ok", "check"], url: "/statamic-emojis/approved.svg" },
    { shortcode: "party-parrot", label: "Party Parrot", keywords: [], url: "/statamic-emojis/party-parrot.svg" },
];
