<?php

namespace Convoy\Emojis\Http\Controllers;

use Convoy\Emojis\CustomEmojis;
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
