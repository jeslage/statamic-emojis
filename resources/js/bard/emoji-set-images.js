// Bard: Unicode emojis shown as images of the site-wide emoji set (like the front-end output).
// Inline decorations put the image behind the emoji and make its glyph transparent, so the document,
// the caret, selection, copy and the saved value keep the real characters.
// An emoji is shown as an image once its image has loaded; emojis the set has no image for stay native.
import { EMOJI_PATTERN, emojiImageUrl } from "../emojibase.js";

const images = new Map(); // emoji → "loading" | "loaded" | "missing"
const views = new Set();

function imageState(emoji, url, refresh) {
    if (!images.has(emoji)) {
        images.set(emoji, "loading");
        const img = new Image();
        img.onload = () => {
            images.set(emoji, "loaded");
            refresh();
        };
        img.onerror = () => images.set(emoji, "missing");
        img.src = url;
    }
    return images.get(emoji);
}

export default function emojiSetImages({ tiptap }) {
    const { Extension } = tiptap.core;
    const { Plugin, PluginKey } = tiptap.pm.state;
    const { Decoration, DecorationSet } = tiptap.pm.view;
    const key = new PluginKey("statamicEmojisSetImages");

    // Re-decorate the open editors once images have loaded (batched)
    let pending = false;
    const refresh = () => {
        if (pending) return;
        pending = true;
        requestAnimationFrame(() => {
            pending = false;
            views.forEach((view) => !view.isDestroyed && view.dispatch(view.state.tr.setMeta(key, true)));
        });
    };

    const decorate = (doc) => {
        if (!emojiImageUrl("😀")) return DecorationSet.empty; // native set

        const decorations = [];
        doc.descendants((node, pos) => {
            if (!node.isText) return;

            for (const match of node.text.matchAll(EMOJI_PATTERN)) {
                const url = emojiImageUrl(match[0]);
                if (imageState(match[0], url, refresh) !== "loaded") continue;

                const from = pos + match.index;
                decorations.push(
                    Decoration.inline(from, from + match[0].length, {
                        class: "bard-emoji-image",
                        style: `background-image: url("${url}")`,
                    }),
                );
            }
        });

        return DecorationSet.create(doc, decorations);
    };

    return Extension.create({
        name: "statamicEmojisSetImages",

        addProseMirrorPlugins() {
            return [
                new Plugin({
                    key,
                    state: {
                        init: (_, state) => decorate(state.doc),
                        apply: (tr, old) => (tr.docChanged || tr.getMeta(key) ? decorate(tr.doc) : old),
                    },
                    props: {
                        decorations: (state) => key.getState(state),
                    },
                    view: (view) => {
                        views.add(view);
                        return { destroy: () => views.delete(view) };
                    },
                }),
            ];
        },
    });
}
