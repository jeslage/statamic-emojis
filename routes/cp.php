<?php

use Convoy\Emojis\Http\Controllers\CustomEmojisController;
use Convoy\Emojis\Http\Controllers\EmojibaseController;
use Convoy\Emojis\Http\Controllers\EmojisPageController;
use Convoy\Emojis\Http\Controllers\ImageCacheController;
use Illuminate\Support\Facades\Route;

Route::get('statamic-emojis/emojibase/{categories}/{locale}/{file}', EmojibaseController::class)
    ->where('categories', 'all|[a-z-]+(,[a-z-]+)*')
    ->where('locale', '[a-z]{2}(-[a-z]+)?')
    ->where('file', 'data\.json|messages\.json')
    ->name('statamic-emojis.emojibase');

Route::get('statamic-emojis/custom', CustomEmojisController::class)->name('statamic-emojis.custom');

// Emojis page (settings, custom emoji grid, image cache tools)
Route::get('statamic-emojis', EmojisPageController::class)->name('statamic-emojis.index');
Route::get('statamic-emojis/cache', [ImageCacheController::class, 'status'])->name('statamic-emojis.cache.status');
Route::post('statamic-emojis/cache/{set}/warm', [ImageCacheController::class, 'warm'])->where('set', '[a-z]+')->name('statamic-emojis.cache.warm');
Route::post('statamic-emojis/cache/{set}/clear', [ImageCacheController::class, 'clear'])->where('set', '[a-z]+')->name('statamic-emojis.cache.clear');
