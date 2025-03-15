<?php

use App\Livewire\QuestionManager;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('site-picker', 'site-picker')
    ->middleware(['auth', 'verified'])
    ->name('site.picker');

Route::middleware(['auth', 'check_site_in_url'])->name('manager.')->group(function () {

    Route::get('dashboard/{site}', function ($site) {
        return view('dashboard', ['site' => $site]);
    })->name('dashboard');

    Volt::route('question-manager/{site}', QuestionManager::class)->name('question');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    //Volt::route('chat-manager/{site}', ChatManager::class)->name('chat-manager');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
