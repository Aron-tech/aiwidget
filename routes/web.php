<?php

use App\Actions\ProcessSuccessfulPayment;
use App\Livewire\ChatManager;
use App\Actions\ViewFileAction;
use App\Livewire\DocumentManager;
use App\Livewire\UserManager;
use App\Livewire\QuestionManager;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Livewire\Volt\Volt;

Route::view('site-picker', 'site-picker')
    ->middleware(['auth', 'verified'])
    ->name('site.picker');

Route::view('/', 'index')->name('home');


Route::middleware(['auth','validate_site_selection', 'verified'])->group(function () {
    Route::get('payment/success', function(Request $request) {
        $session_id = $request->get('session_id');
        return ProcessSuccessfulPayment::run($session_id);
    });

    Volt::route('dashboard', 'dashboard')->name('dashboard');

    Volt::route('generate-widget', 'generatewidget')->name('generate-widget');

    Route::get('view-file', ViewFileAction::class)->name('view-file');

    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
    Volt::route('settings/language', 'settings.language')->name('settings.language');
});

Route::middleware(['auth', 'validate_site_selection', 'verified'])->name('manager.')->group(function () {
    Volt::route('question-manager', QuestionManager::class)->name('question');

    Volt::route('chat-manager', ChatManager::class)->name('chat');

    Volt::route('user-manager', UserManager::class)->name('user');

    Volt::route('document-manager', DocumentManager::class)->name('document');
});

//Set-locale API
Route::post('/set-locale', function(Request $request) {
    $locale = $request->input('locale', config('app.locale'));
    App::setLocale($locale);
    Session::put('locale', $locale);
    return response()->noContent();
});

require __DIR__.'/auth.php';
