// Emoji search for shortcode suggestions (typing ":rock" in Emoji Text and Bard).
// Uses the same Emojibase data as the picker (through the addon's CP route) plus custom emojis.
import { emojibaseUrl } from "./emojibase.js";
import { cpEmojibaseLocale } from "./locale.js";
import { loadCustomEmojis, useCustomEmojis } from "./custom-emojis.js";

const COMPONENT_GROUP = 2; // skin tone and hair components, never suggested
const FLAGS_GROUP = 9;
const DEFAULT_MAX_VERSION = 15;
const TONES = { light: 1, "medium-light": 2, medium: 3, "medium-dark": 4, dark: 5 };

/**
 * ":query" right before the cursor, at least 2 characters. Anywhere except glued to a letter or digit:
 * so right after an emoji or custom emoji works ("👍:rock"), times and URLs ("10:30", "a:bc") don't.
 */
const TRIGGER = /(?:^|[^\p{L}\p{N}]):([a-z0-9_+-]{2,})$/iu;

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

function getJson(url, fallback) {
    return fetch(url, {
        headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" },
        credentials: "same-origin",
    })
        .then((response) => (response.ok ? response.json() : fallback))
        .catch(() => fallback);
}

/** Shortcodes of an Emojibase hexcode as an array ("rocket" or ["+1", "thumbsup"]). */
const shortcodesOf = (shortcodes, hexcode) => [shortcodes[hexcode] ?? []].flat();

/** Loads the emoji data (CP locale, all categories) and the English shortcodes once. */
export function loadEmojiIndex() {
    loadCustomEmojis();

    loading ??= Promise.all([
        getJson(`${emojibaseUrl([])}/${cpEmojibaseLocale()}/data.json`, []),
        getJson(`${Statamic.$config.get("cpUrl")}/statamic-emojis/emojibase/shortcodes.json`, {}),
    ]).then(([data, shortcodes]) => {
        const { maxVersion, flags } = deviceSupport();

        index = data
            .filter((emoji) => emoji.emoji && emoji.group !== undefined && emoji.group !== COMPONENT_GROUP)
            .filter((emoji) => (emoji.version ?? 0) <= maxVersion && (flags || emoji.group !== FLAGS_GROUP))
            .map((emoji) => ({
                emoji: emoji.emoji,
                label: emoji.label ?? "",
                tags: emoji.tags ?? [],
                shortcodes: shortcodesOf(shortcodes, emoji.hexcode),
                skins: (emoji.skins ?? [])
                    .filter((skin) => typeof skin.tone === "number")
                    .map((skin) => ({ ...skin, shortcodes: shortcodesOf(shortcodes, skin.hexcode) })),
            }));

        return index;
    });

    return loading;
}

/**
 * Emojis matching a query, best first: custom emojis by shortcode, then emojis whose shortcode starts with
 * the query, shortcodes with a part starting with it, names starting with it, names with a word starting
 * with it, tags starting with it, and names containing it.
 * Returns [{ emoji, label, shortcode }]: custom emojis are ":shortcode:" as emoji, label is the name.
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
                results.push({ score, emoji: `:${custom.shortcode}:`, label: custom.label, shortcode: custom.shortcode });
            }
        }
    }

    const tone = rememberedTone();

    for (const entry of index ?? []) {
        const label = entry.label.toLowerCase();
        const prefixShortcode = entry.shortcodes.find((code) => code.startsWith(q));
        const partShortcode = entry.shortcodes.find((code) => code.split(/[_-]/).some((part) => part.startsWith(q)));
        const score = prefixShortcode
            ? 3
            : partShortcode
              ? 4
              : label.startsWith(q)
                ? 5
                : label.split(/[\s:,-]+/).some((word) => word.startsWith(q))
                  ? 6
                  : entry.tags.some((tag) => tag.startsWith(q))
                    ? 7
                    : label.includes(q)
                      ? 8
                      : null;

        if (score !== null) {
            // Show the shortcode that matched, in the editor's skin tone if the emoji has one
            const skin = tone ? entry.skins.find((s) => s.tone === tone) : null;
            const base = prefixShortcode ?? partShortcode ?? entry.shortcodes[0] ?? null;
            const shortcode = skin && base ? (skin.shortcodes.find((code) => code.startsWith(base)) ?? base) : base;

            results.push({ score, emoji: skin?.emoji ?? entry.emoji, label: skin?.label ?? entry.label, shortcode });
        }
    }

    return results
        .sort((a, b) => a.score - b.score || a.label.length - b.label.length)
        .slice(0, limit)
        .map(({ score, ...item }) => item);
}
