<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div class="flex flex-col items-start">
    @include('partials.settings-heading')

    <x-settings.layout heading="{{ __('interface.appearance_title') }}" subheading="{{ __('interface.appearance_description') }}">
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
            <flux:radio value="light" icon="sun">{{ __('interface.light_theme') }}</flux:radio>
            <flux:radio value="dark" icon="moon">{{ __('interface.dark_theme') }}</flux:radio>
            <flux:radio value="system" icon="computer-desktop">{{ __('interface.system_theme') }}</flux:radio>
        </flux:radio.group>
    </x-settings.layout>
</div>
