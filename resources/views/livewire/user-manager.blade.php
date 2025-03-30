<div>
    <!-- Értesítés megjelenítése -->
    <x-notification.panel :notifications="session()->all()"/>
    <div class="grid grid-cols-4 gap-6">
        <!--Táblázat fejléc-->
        <div class="pb-3 dark:border-white/20 border-b-zinc-900/20 border-b-2">
            <flux:heading size="lg">{{__('interface.token_enscrypted')}}</flux:heading>
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