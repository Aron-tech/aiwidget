<?php

use App\Livewire\ChatManager;
use App\Livewire\UserManager;
use App\Livewire\QuestionManager;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::redirect('/', 'login')->name('home');

Route::view('site-picker', 'site-picker')
    ->middleware(['auth', 'verified'])
    ->name('site.picker');


Route::middleware(['auth','validate_site_selection'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Volt::route('generate-widget', 'generatewidget')->name('generate-widget');

    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
    Volt::route('settings/language', 'settings.language')->name('settings.language');
});

Route::middleware(['auth', 'validate_site_selection'])->name('manager.')->group(function () {
    Volt::route('question-manager', QuestionManager::class)->name('question');

    Volt::route('chat-manager', ChatManager::class)->name('chat');

    Volt::route('user-manager', UserManager::class)->name('user');

    //Volt::route('chat-manager', ChatManager::class)->name('chat');
});

require __DIR__.'/auth.php';
