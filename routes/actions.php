<?php

use StatamicEmojis\Http\Controllers\EmojiImageController;
use Illuminate\Support\Facades\Route;

Route::get('sets/{set}/{codes}.svg', EmojiImageController::class)
    ->where('set', '[a-z]+')
    ->where('codes', '[0-9a-f]{1,6}(-[0-9a-f]{1,6}){0,15}')
    ->name('statamic-emojis.image');
