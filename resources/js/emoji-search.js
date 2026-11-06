// Emoji search for shortcode suggestions (typing ":rock" in Emoji Text and Bard).
// Uses the same Emojibase data as the picker (through the addon's CP route) plus custom emojis.
import { emojibaseUrl } from "./emojibase.js";
import { cpEmojibaseLocale } from "./locale.js";
import { loadCustomEmojis, useCustomEmojis } from "./custom-emojis.js";

const COMPONENT_GROUP = 2; // skin tone and hair components, never suggested
const FLAGS_GROUP = 9;
const DEFAULT_MAX_VERSION = 15;
const TONES = { light: 1, "medium-light": 2, medium: 3, "medium-dark": 4, dark: 5 };

/** ":query" right before the cursor, at the start or after whitespace; at least 2 characters. */
const TRIGGER = /(?:^|\s):([a-z0-9_+-]{2,})$/i;

let index = null;
let loading = null;

/** The trigger in the text before the cursor: { query, length } (length includes the colon), or null. */
export function findTrigger(textBeforeCursor) {
    const match = TRIGGER.exec(textBeforeCursor ?? "");
    return match ? { query: match[1].toLowerCase(), length: match[1].length + 1 } : null;
}

/** What the device can render, as detected by the picker (frimousse) in this session. */
function deviceSupport() {
    try {
        const metadata = JSON.parse(sessionStorage.getItem("frimousse/metadata") ?? "null");
        return {
            maxVersion: typeof metadata?.emojiVersion === "number" ? metadata.emojiVersion : DEFAULT_MAX_VERSION,
            flags: metadata?.countryFlags !== false,
        };
    } catch {
        return { maxVersion: DEFAULT_MAX_VERSION, flags: true };
    }
}

function rememberedTone() {
    try {
        return TONES[localStorage.getItem("statamic-emojis/skin-tone")] ?? null;
    } catch {
        return null;
    }
}

/** Loads the emoji data once (CP locale, all categories). */
export function loadEmojiIndex() {
    loadCustomEmojis();

    loading ??= fetch(`${emojibaseUrl([])}/${cpEmojibaseLocale()}/data.json`, {
        headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" },
        credentials: "same-origin",
    })
        .then((response) => (response.ok ? response.json() : []))
        .catch(() => [])
        .then((data) => {
            const { maxVersion, flags } = deviceSupport();

            index = data
                .filter((emoji) => emoji.emoji && emoji.group !== undefined && emoji.group !== COMPONENT_GROUP)
                .filter((emoji) => (emoji.version ?? 0) <= maxVersion && (flags || emoji.group !== FLAGS_GROUP))
                .map((emoji) => ({
                    emoji: emoji.emoji,
                    label: emoji.label ?? "",
                    tags: emoji.tags ?? [],
                    skins: (emoji.skins ?? []).filter((skin) => typeof skin.tone === "number"),
                }));

            return index;
        });

    return loading;
}

/**
 * Emojis matching a query, best first: custom emojis by shortcode, then names starting with the query,
 * names with a word starting with it, tags starting with it, and names containing it.
 * Returns [{ emoji, label, hint }] where custom emojis are ":shortcode:".
 */
export function searchEmojis(query, { customEmojis = true, limit = 8 } = {}) {
    const q = String(query ?? "").toLowerCase();
    if (!q) return [];

    const results = [];

    if (customEmojis) {
        for (const custom of useCustomEmojis().list) {
            const keywords = [custom.label, ...(custom.keywords ?? [])].map((k) => String(k).toLowerCase());
            const score = custom.shortcode.startsWith(q)
                ? 0
                : custom.shortcode.includes(q)
                  ? 1
                  : keywords.some((k) => k.includes(q))
                    ? 2
                    : null;

            if (score !== null) {
                results.push({ score, emoji: `:${custom.shortcode}:`, label: custom.label, hint: `:${custom.shortcode}:` });
            }
        }
    }

    const tone = rememberedTone();

    for (const entry of index ?? []) {
        const label = entry.label.toLowerCase();
        const score = label.startsWith(q)
            ? 3
            : label.split(/[\s:,-]+/).some((word) => word.startsWith(q))
              ? 4
              : entry.tags.some((tag) => tag.startsWith(q))
                ? 5
                : label.includes(q)
                  ? 6
                  : null;

        if (score !== null) {
            const skin = tone ? entry.skins.find((s) => s.tone === tone) : null;
            results.push({ score, emoji: skin?.emoji ?? entry.emoji, label: entry.label, hint: "" });
        }
    }

    return results
        .sort((a, b) => a.score - b.score || a.label.length - b.label.length)
        .slice(0, limit)
        .map(({ score, ...item }) => item);
}
