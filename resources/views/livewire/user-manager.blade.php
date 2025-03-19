<div>
    <!-- Értesítés megjelenítése -->
    <div wire:key='notification' class="fixed top-3 sm:top-5 w-2/3 sm:left-1/2 transform sm:-translate-x-1/2 sm:w-1/4 z-50">
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
    <div class="sm:block hidden mb-4">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{route('dashboard', $site->uuid)}}" icon="home" />
            <flux:breadcrumbs.item>{{__('interface.user_manager')}}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </div>
    <div class="flex space-x-2">
        <div class="flex w-1/2 mb-12 mr-4" x-data @keydown.window.prevent.meta.k="$refs.searchInput.focus()" @keydown.window.prevent.ctrl.k="$refs.searchInput.focus()">
                <flux:input
                    wire:model.live='search'
                    x-ref="searchInput"
                    kbd="⌘K"
                    icon="magnifying-glass"
                    placeholder="{{ __('interface.search') }}"
                />
        </div>
        <div class="flex space-x-4 justify-end w-full">
            <flux:modal.trigger name="info-modal">
                <flux:button icon="information-circle">{{__('interface.info')}}</flux:button>
            </flux:modal.trigger>
            <flux:button wire:click='reloadKeys()' class="ml-4" icon="arrow-path" variant="filled"></flux:button>
            <flux:button wire:click='create()' icon="plus">{{__('interface.add')}}</flux:button>
        </div>
    </div>
    <div class="grid grid-cols-4 gap-6">
        <!--Táblázat fejléc-->
        <div class="pb-3 dark:border-white/20 border-b-zinc-900/20 border-b-2">
            <flux:heading size="lg">{{__('interface.token')}}</flux:heading>
        </div>
        <div class="pb-3 dark:border-white/20 border-b-zinc-900/20 border-b-2">
            <flux:heading size="lg">{{__('interface.name')}}</flux:heading>
        </div>
        <div class="pb-3 dark:border-white/20 border-b-zinc-900/20 border-b-2">
            <flux:heading size="lg">{{__('interface.email')}}</flux:heading>
        </div>
        <div class="pb-3 dark:border-white/20 border-b-zinc-900/20 border-b-2">
            <flux:heading size="lg">{{__('interface.actions')}}</flux:heading>
        </div>

        <!--Táblázat adatok-->
        @foreach ($keys as $key)
            @if($key->type === 0)
                <div class="overflow-hidden">
                    <flux:heading>{{$key?->token}}</flux:heading>
                </div>
                <div>
                    <flux:heading>{{$key?->user?->name}}</flux:heading>
                </div>
                <div>
                    <flux:heading>{{$key?->user?->email}}</flux:heading>
                </div>
                <div class="flex justify-center space-x-4">
                    <flux:button wire:click='delete({{ $key?->id }})' icon="trash" variant="danger"></flux:button>
                </div>
            @endif
        @endforeach
        <div class="col-span-full">
            {{ $keys->links() }}
        </div>
    </div>

    <!--Livewire komponensek-->
    @livewire('key.create')
    @livewire('key.delete')
    @livewire('key.info')
</div>