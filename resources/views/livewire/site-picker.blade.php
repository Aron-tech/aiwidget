<div>
    <div class="fixed top-3 sm:top-5 w-2/3 sm:left-1/2 transform sm:-translate-x-1/2 sm:w-1/4 z-50">
        @if(session()->has('success'))
            <flux:callout icon="check-circle" variant="success" inline x-data="{ visible: true }" x-show="visible">
                <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start">{{ session('success') }}</flux:callout.heading>
                <x-slot name="controls">
                    <flux:button icon="x-mark" variant="ghost" x-on:click="visible = false" />
                </x-slot>
            </flux:callout>
        @elseif(session()->has('warning'))
            <flux:callout icon="exclamation-triangle" variant="warning" inline x-data="{ visible: true }" x-show="visible">
                <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start">{{ session('warning') }}</flux:callout.heading>
                <x-slot name="controls">
                    <flux:button icon="x-mark" variant="ghost" x-on:click="visible = false" />
                </x-slot>
            </flux:callout>
        @elseif(session()->has('danger'))
            <flux:callout icon="exclamation-triangle" variant="danger" inline x-data="{ visible: true }" x-show="visible">
                <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start">{{ session('danger') }}</flux:callout.heading>
                <x-slot name="controls">
                    <flux:button icon="x-mark" variant="ghost" x-on:click="visible = false" />
                </x-slot>
            </flux:callout>
        @elseif(session()->has('info'))
            <flux:callout icon="exclamation-triangle" variant="info" inline x-data="{ visible: true }" x-show="visible">
                <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start">{{ session('info') }}</flux:callout.heading>
                <x-slot name="controls">
                    <flux:button icon="x-mark" variant="ghost" x-on:click="visible = false" />
                </x-slot>
            </flux:callout>
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 2xl:grid-cols-8 grid-rows-auto gap-6 items-center justify-items-center min-h-screen p-8 pt-0">


        <!-- Címsor -->
        <div class="col-span-full">
            <x-text.h1>{{ __('interface.select_website') }}</x-text.h1>
        </div>

        <!-- Weboldal kártyák -->
    @foreach ($sites as $site)
        <x-cards.sitepick wire:key='site-key-{{ $site->id }}' :$auth_user :$site/>
    @endforeach
    <flux:modal.trigger name="add-site">
            <div class="flex flex-col justify-center items-center col-span-1 sm:col-span-2 row-span-2 bg-white dark:bg-black/10 rounded-lg shadow-md w-full h-full p-6 hover:shadow-lg transition duration-300 border border-gray-200 dark:border-gray-700">
                <button class="text-4xl sm:text-5xl md:text-6xl text-gray-500 dark:text-white font-bold">+</button>
                <p class="text-gray-500 dark:text-white text-sm sm:text-base md:text-lg mt-2 uppercase">{{ __('interface.add_new_website')}}</p>
            </div>
        </flux:modal.trigger>
    </div>

    <!-- Livewire komponensek -->
    @livewire('site.create')
    @livewire('site.edit')
    @livewire('site.delete')
</div>

