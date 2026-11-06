// Recently picked emojis per editor, stored in their browser.
import { ref } from "vue";

const STORAGE_KEY = "statamic-emojis/recent";
const MAX = 27; // three rows

function read() {
    try {
        const list = JSON.parse(localStorage.getItem(STORAGE_KEY) ?? "[]");
        return Array.isArray(list) ? list.filter((item) => typeof item?.emoji === "string") : [];
    } catch {
        return [];
    }
}

const recent = ref(read());

/** Reactive list of { emoji, label }, most recent first. Custom emojis are stored as ":shortcode:". */
export function useRecentEmojis() {
    return recent;
}

export function rememberEmoji(emoji, label) {
    recent.value = [{ emoji, label: label ?? emoji }, ...recent.value.filter((item) => item.emoji !== emoji)].slice(0, MAX);

    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(recent.value));
    } catch {
        // Storage unavailable: keep the list for this page view only
    }
}
