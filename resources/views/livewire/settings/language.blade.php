<?php

use Livewire\Volt\Component;

new class extends Component {
    public $language = 'en';

    public function mount()
    {
        $this->language = getJsonValue(auth()->user(), 'other_data', 'locale', 'en');
    }

    public function updatedLanguage()
    {
        session()->put('locale', $this->language);
        app()->setLocale($this->language);
        setJsonValue(auth()->user(), 'other_data', 'locale', $this->language);
        return redirect()->route('settings.language')->with('success', __('language.updated'));
    }
}; ?>

<div class="flex flex-col items-start">
    @include('partials.settings-heading')
    <x-notification.panel :notifications="session()->all()"/>
    <x-settings.layout heading="{{ __('language.languages') }}" subheading="{{ __('language.select') }}">
        <flux:radio.group wire:model.live="language" label="{{ __('language.languages') }}" variant="segmented">
            <flux:radio value="hu" label="{{ __('language.hu') }}" />
            <flux:radio value="en" label="{{ __('language.en') }}" />
            <flux:radio value="de" label="{{ __('language.de') }}" />
        </flux:radio.group>
    </x-settings.layout>
</div>
