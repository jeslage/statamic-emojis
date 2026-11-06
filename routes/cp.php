<?php

use Convoy\Emojis\Http\Controllers\EmojibaseController;
use Illuminate\Support\Facades\Route;

Route::get('statamic-emojis/emojibase/{categories}/{locale}/{file}', EmojibaseController::class)
    ->where('categories', 'all|[a-z-]+(,[a-z-]+)*')
    ->where('locale', '[a-z]{2}(-[a-z]+)?')
    ->where('file', 'data\.json|messages\.json')
    ->name('statamic-emojis.emojibase');
