<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\App;

new class extends Component {
    public string $locale;
    public bool $dropdown = false;

    public bool $reload_page = false;

    public array $flag_paths = [
            'hu' => 'flags/hu.svg',
            'en' => 'flags/gb.svg',
            'de' => 'flags/de.svg',
        ];

    public function mount(bool $reload_page = false): void
    {
        $this->locale = session('locale', config('app.locale'));
        App::setLocale($this->locale);
        $this->reload_page = $reload_page;
    }

    public function setLocale(string $locale)
    {
        session()->put('locale', $locale);
        App::setLocale($locale);

        $this->locale = $locale;
        $this->dropdown = false;
        if($this->reload_page) $this->dispatch('reload-page');
    }
};
?>

<div class="language-selector">
    <button class="language-button" wire:click="$toggle('dropdown')">
        <img src="{{ asset($flag_paths[$locale ?? 'en']) }}" class="w-6 h-6" alt="{{strtoupper($locale ?? 'en')}}">
        {{ strtoupper($locale) }}
        <svg class="w-4 h-4 transition-transform duration-200 {{ $dropdown ? 'rotate-180' : '' }}"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    @if($dropdown)
        <div class="language-dropdown show">
            <div class="language-option {{ $locale === 'hu' ? 'active' : '' }}" wire:click="setLocale('hu')">
                <img src="{{ asset($flag_paths['hu']) }}" class="w-6 h-6" alt="HU">
                {{__('language.hu')}}
            </div>
            <div class="language-option {{ $locale === 'en' ? 'active' : '' }}" wire:click="setLocale('en')">
                <img src="{{ asset($flag_paths['en']) }}" class="w-6 h-6" alt="EN">
                {{__('language.en')}}
            </div>
            <div class="language-option {{ $locale === 'de' ? 'active' : '' }}" wire:click="setLocale('de')">
                <img src="{{ asset($flag_paths['de']) }}" class="w-6 h-6" alt="DE">
                {{__('language.de')}}
            </div>
        </div>
    @endif
</div>
<script>
    document.addEventListener('reload-page', () => {
        window.location.reload();
    });
</script>
