<div>
    <x-notification.panel :notifications="session()->all()"/>

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

