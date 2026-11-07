<?php

namespace StatamicEmojis\Http\Controllers;

use StatamicEmojis\CustomEmojis;
use Illuminate\Http\JsonResponse;

class CustomEmojisController
{
    public function __invoke(CustomEmojis $custom): JsonResponse
    {
        return response()->json($custom->all()->values());
    }
}
