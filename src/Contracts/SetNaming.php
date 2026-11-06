<?php

namespace Convoy\Emojis\Contracts;

/**
 * File naming of a custom emoji image set, for sets that don't fit the built-in schemes
 * ("noto", "fluent", "openmoji"). Use the class name as the set's "naming".
 */
interface SetNaming
{
    /**
     * File paths (relative to the set's url) to try for an emoji, in order.
     *
     * @param  array<int, string>  $codepoints  Lowercase hex codepoints, e.g. ['1f9d1', '200d', '1f680'].
     *                                          Called with and without the variation selector "fe0f".
     * @return array<int, string>
     */
    public function files(array $codepoints): array;
}
