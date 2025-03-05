<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:1000,1', \App\Http\Middleware\VerifyReferer::class])->name('widget.')->group(function () {

    Route::post('/submit-message/{site:uuid}', [MessageController::class, 'storeUserMessage'])->name('store');
    Route::get('/messages/{site:uuid}', [ChatController::class, 'show'])->name('show');
    Route::delete('/messages/delete/{site:uuid}', [ChatController::class, 'delete'])->name('delete');

});
