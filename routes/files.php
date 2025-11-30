<?php
use Illuminate\Support\Facades\Route;

Route::get('/privacy-policy', function () {
    $path = Storage::disk('public')->path('docs/adatkezelesi_tajekoztato.pdf');

    abort_unless(file_exists($path), 404);

    return response()->file($path, [
        'Content-Type' => 'application/pdf',
    ]);
})->name('privacy-policy');

Route::get('/terms-and-conditions', function () {
    $path = Storage::disk('public')->path('docs/aszf.pdf');

    abort_unless(file_exists($path), 404);

    return response()->file($path, [
        'Content-Type' => 'application/pdf',
    ]);
})->name('terms-and-conditions');
