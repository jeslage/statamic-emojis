<?php

namespace StatamicEmojis\Contracts;

interface SetNaming
{
    /**
     * @param  array<int, string>  $codepoints
     * @return array<int, string>
     */
    public function files(array $codepoints): array;
}
