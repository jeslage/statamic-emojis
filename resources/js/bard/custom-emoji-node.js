import { findCustom, loadCustomEmojis } from "../custom-emojis.js";

export default function customEmojiNode({ tiptap }) {
    const { Node, mergeAttributes } = tiptap.core;

    return Node.create({
        name: "customEmoji",
        group: "inline",
        inline: true,
        atom: true,
        selectable: true,
        draggable: false,

        addAttributes() {
            return {
                shortcode: {
                    default: null,
                    parseHTML: (element) => element.getAttribute("data-custom-emoji"),
                    renderHTML: (attributes) => ({ "data-custom-emoji": attributes.shortcode }),
                },
            };
        },

        parseHTML() {
            return [{ tag: "img[data-custom-emoji]" }];
        },

        renderHTML({ node, HTMLAttributes }) {
            const emoji = findCustom(node.attrs.shortcode);

            return [
                "img",
                mergeAttributes(HTMLAttributes, {
                    class: "emoji emoji-custom",
                    src: emoji?.url ?? "",
                    alt: emoji?.label ?? `:${node.attrs.shortcode}:`,
                }),
            ];
        },

        renderText({ node }) {
            return `:${node.attrs.shortcode}:`;
        },

        addNodeView() {
            return ({ node }) => {
                const dom = document.createElement("span");
                dom.className = "bard-custom-emoji";
                dom.setAttribute("data-custom-emoji", node.attrs.shortcode);
                dom.title = `:${node.attrs.shortcode}:`;
                dom.textContent = `:${node.attrs.shortcode}:`;

                loadCustomEmojis().then(() => {
                    const emoji = findCustom(node.attrs.shortcode);
                    if (!emoji) return;

                    const img = document.createElement("img");
                    img.src = emoji.url;
                    img.alt = emoji.label;
                    img.draggable = false;
                    dom.replaceChildren(img);
                });

                return { dom };
            };
        },
    });
}
