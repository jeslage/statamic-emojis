// Bard: shortcode suggestions while typing ":rock" (only in Bard fields with the emoji button).
// A ProseMirror plugin finds the trigger before the cursor; a single suggestions list is mounted in
// <body> (its own small Vue app) and positioned at the cursor. ↑/↓ choose, Enter/Tab insert, Escape closes.
import { createApp, h, reactive } from "vue";
import EmojiSuggestions from "../components/EmojiSuggestions.vue";
import { findTrigger, loadEmojiIndex, searchEmojis } from "../emoji-search.js";
import { isShortcode } from "../custom-emojis.js";
import { pickerOptions, siteSettings } from "../settings.js";
import { rememberEmoji } from "../recent-emojis.js";

let dropdown = null;
let editors = 0;

/** The shared suggestions list; only one Bard editor has focus at a time. */
function sharedDropdown() {
    if (dropdown) return dropdown;

    dropdown = reactive({
        visible: false,
        owner: null,
        items: [],
        active: 0,
        top: 0,
        left: 0,
        onSelect: null,
        reposition: null,
    });

    // Keep the list at the cursor while the page or a stack scrolls (fixed position follows the viewport)
    const reposition = () => dropdown.visible && dropdown.reposition?.();
    window.addEventListener("scroll", reposition, true);
    window.addEventListener("resize", reposition);

    const el = document.createElement("div");
    el.className = "statamic-emojis-bard-suggestions";
    document.body.appendChild(el);

    createApp({
        render: () =>
            dropdown.visible
                ? h(
                      "div",
                      { style: { position: "fixed", top: `${dropdown.top}px`, left: `${dropdown.left}px`, zIndex: 9999 } },
                      [
                          h(EmojiSuggestions, {
                              items: dropdown.items,
                              active: dropdown.active,
                              onSelect: (item) => dropdown.onSelect?.(item),
                              onHover: (index) => (dropdown.active = index),
                          }),
                      ],
                  )
                : null,
    }).mount(el);

    return dropdown;
}

export default function emojiSuggestions({ bard, tiptap }) {
    if (!(bard?.config?.buttons ?? []).includes("emoji")) return [];

    const { Extension } = tiptap.core;
    const { Plugin, PluginKey } = tiptap.pm.state;

    return Extension.create({
        name: "statamicEmojisSuggestions",
        // Before keymaps (Enter = new paragraph) so the keys reach the open list first
        priority: 1000,

        addProseMirrorPlugins() {
            const list = sharedDropdown();
            // This editor's id; only the editor showing the list may hide it. A number, not an object:
            // objects stored in the reactive list become proxies and would never compare equal.
            const owner = ++editors;
            let range = null;
            let request = 0;

            const close = () => {
                request++;
                range = null;
                if (list.owner === owner) list.visible = false;
            };

            const select = (view, item) => {
                if (!range || !item) return;

                const { from, to } = range;
                const { state } = view;
                const tr = isShortcode(item.emoji) && state.schema.nodes.customEmoji
                    ? state.tr.replaceWith(from, to, state.schema.nodes.customEmoji.create({ shortcode: item.emoji.slice(1, -1) }))
                    : state.tr.insertText(item.emoji, from, to);

                view.dispatch(tr.scrollIntoView());
                view.focus();
                rememberEmoji(item.emoji, item.label);
                close();
            };

            const position = (view) => {
                if (!range || list.owner !== owner) return;
                const coords = view.coordsAtPos(range.from);
                list.top = coords.bottom + 4;
                list.left = coords.left;
            };

            const update = (view) => {
                const { selection } = view.state;

                if (!view.hasFocus() || !selection.empty || siteSettings().suggestions === false) return close();

                const $from = selection.$from;
                if (!$from.parent.isTextblock) return close();

                const before = $from.parent.textBetween(Math.max(0, $from.parentOffset - 50), $from.parentOffset, null, "￼");
                const trigger = findTrigger(before);
                if (!trigger) return close();

                const from = selection.from - trigger.length;
                const to = selection.from;
                const current = ++request;

                loadEmojiIndex().then(() => {
                    if (current !== request) return;

                    const items = searchEmojis(trigger.query, { customEmojis: pickerOptions(bard.config ?? {}).customEmojis });
                    if (!items.length) return close();

                    range = { from, to };
                    Object.assign(list, {
                        owner,
                        items,
                        active: 0,
                        visible: true,
                        onSelect: (item) => select(view, item),
                        reposition: () => position(view),
                    });
                    position(view);
                });
            };

            // Keys while the list is open. Listened to in the capture phase on the editor element: Bard's own
            // editor-level key handling runs before plugin props and would otherwise take Enter and Tab.
            const handleKey = (view, event) => {
                if (!list.visible || !range || list.owner !== owner) return false;

                const count = list.items.length;
                if (event.key === "ArrowDown") list.active = (list.active + 1) % count;
                else if (event.key === "ArrowUp") list.active = (list.active - 1 + count) % count;
                else if (event.key === "Enter" || event.key === "Tab") select(view, list.items[list.active]);
                else if (event.key === "Escape") close();
                else return false;

                return true;
            };

            return [
                new Plugin({
                    key: new PluginKey("statamicEmojisSuggestions"),
                    view: (editorView) => {
                        const onKeydown = (event) => {
                            if (handleKey(editorView, event)) {
                                event.preventDefault();
                                event.stopPropagation();
                            }
                        };

                        editorView.dom.addEventListener("keydown", onKeydown, true);

                        return {
                            update,
                            destroy: () => {
                                editorView.dom.removeEventListener("keydown", onKeydown, true);
                                close();
                            },
                        };
                    },
                    props: {
                        handleDOMEvents: {
                            blur: () => {
                                close();
                                return false;
                            },
                        },
                    },
                }),
            ];
        },
    });
}
