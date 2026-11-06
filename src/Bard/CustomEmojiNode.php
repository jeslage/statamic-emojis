<?php

namespace StatamicEmojis\Bard;

use StatamicEmojis\CustomEmojis;
use Tiptap\Core\Node;

/**
 * Bard inline node for custom emojis: {"type": "customEmoji", "attrs": {"shortcode": "party-parrot"}}.
 * Renders an <img> on the front end, or ":shortcode:" as text if the emoji no longer exists.
 */
class CustomEmojiNode extends Node
{
    public static $name = 'customEmoji';

    public function addAttributes()
    {
        return [
            'shortcode' => [
                'parseHTML' => fn ($DOMNode) => $DOMNode->getAttribute('data-custom-emoji'),
                'renderHTML' => fn () => null, // rendered by renderHTML() below
            ],
        ];
    }

    public function parseHTML()
    {
        return [['tag' => 'img[data-custom-emoji]']];
    }

    public function renderHTML($node, $HTMLAttributes = [])
    {
        $shortcode = $node->attrs->shortcode ?? '';
        $emoji = app(CustomEmojis::class)->find($shortcode);

        return ['content' => $emoji ? app(CustomEmojis::class)->img($emoji) : e(":{$shortcode}:")];
    }
}
