<?php

use App\Livewire\UserManager;
use App\Livewire\QuestionManager;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('site-picker', 'site-picker')
    ->middleware(['auth', 'verified'])
    ->name('site.picker');


Route::middleware(['auth', 'check_site_in_url'])->group(function () {
    Route::get('dashboard/{site}', function ($site) {
        return view('dashboard', ['site' => $site]);
    })->name('dashboard');

    Volt::route('generate-widget/{site}', 'generatewidget')->name('generate-widget');
});

Route::middleware(['auth', 'check_site_in_url'])->name('manager.')->group(function () {
    Volt::route('question-manager/{site}', QuestionManager::class)->name('question');

    Volt::route('user-manager/{site}', UserManager::class)->name('user');

});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    //Volt::route('chat-manager/{site}', ChatManager::class)->name('chat-manager');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
    Volt::route('settings/language', 'settings.language')->name('settings.language');
});

require __DIR__.'/auth.php';
