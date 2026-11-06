<?php

namespace StatamicEmojis\Http\Controllers;

use StatamicEmojis\CustomEmojis;
use Illuminate\Http\JsonResponse;

/**
 * Custom emojis for the CP picker: [{shortcode, label, keywords, url}].
 */
class CustomEmojisController
{
    public function __invoke(CustomEmojis $custom): JsonResponse
    {
        return response()->json($custom->all()->values());
    }
}
