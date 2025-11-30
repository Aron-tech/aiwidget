<div>
    <x-notification.panel :notifications="session()->all()"/>
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
            <flux:dropdown>
                <flux:button icon-trailing="funnel"></flux:button>

                <flux:menu>
                    <flux:menu.radio.group wire:model.live='filter'>
                        <flux:menu.radio value="0">{{ __('interface.all')}}</flux:menu.radio>
                        <flux:menu.radio value="1">{{ __('interface.activated')}}</flux:menu.radio>
                        <flux:menu.radio value="2">{{ __('interface.not_activated')}}</flux:menu.radio>
                    </flux:menu.radio.group>
                </flux:menu>
            </flux:dropdown>
        </div>
    </div>
    <div class="grid grid-cols-4 gap-6">
        <div class="pb-3 dark:border-white/20 border-b-zinc-900/20 border-b-2">
            <flux:heading size="lg">{{__('interface.avatar')}}</flux:heading>
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

        @foreach ($keys as $key)
            @if($key->type === App\Enums\KeyTypesEnum::MODERATOR)
                <div class="overflow-hidden">
                    <x-avatar :src="$key->user?->image" size="48" :fallback="Str::upper(Str::substr($key->user?->name, 0, 1))"/>
                </div>
                <div class="flex items-center">
                    <flux:heading>{{$key->user?->name}}</flux:heading>
                </div>
                <div class="flex items-center">
                    <flux:heading>{{$key->user?->email}}</flux:heading>
                </div>
                <div class="flex justify-center space-x-4">
                    <flux:button wire:click='edit({{ $key->id }})' icon="pencil-square" variant="filled"></flux:button>
                    <flux:button wire:click='delete({{ $key->id }})' icon="trash" variant="danger"></flux:button>
                </div>
            @endif
        @endforeach
        <div class="col-span-full">
            {{ $keys->links() }}
        </div>
    </div>

    <!--Livewire komponensek-->
    @livewire('key.create')
    @livewire('key.edit')
    @livewire('key.delete')
    @livewire('key.info')
</div>
