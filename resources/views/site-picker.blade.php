<x-layouts.app>
    @livewire('site-picker')
    <div class="w-full flex relative min-h-12 m-0 p-0">
        <form method="POST" action="{{ route('logout') }}" class="absolute right-0 bottom-230">
            @csrf
            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle">
                {{ __('interface.logout') }}
            </flux:menu.item>
        </form>
    </div>
</x-layouts.app>
