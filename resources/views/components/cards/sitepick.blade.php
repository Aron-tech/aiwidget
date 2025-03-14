@props(['site', 'auth_user'])
<div class="relative flex flex-col justify-between items-center col-span-1 sm:col-span-2
row-span-2 bg-white dark:bg-black/10 rounded-lg shadow-md w-full h-full p-6 hover:shadow-lg transition duration-300 border border-gray-200 dark:border-gray-700">
    <div class="absolute top-4 right-4 flex gap-2">
        @if($site->keys[0]->user_id=== $auth_user->id)
            <flux:button wire:click='edit({{$site->id}})' icon="pencil-square" variant="filled"></flux:button>
        @endif
        <flux:button wire:click='delete({{$site->id}})' icon="trash" variant="danger"></flux:button>
    </div>

    <div class="flex flex-col justify-center items-center flex-grow">
        <x-text.h2>{{$site->name}}</x-text.h2>
        <a href="{{ route('dashboard', $site->uuid) }}" class="bg-gray-800 dark:bg-gray-700 text-white px-4 sm:px-6 py-2 rounded-lg font-medium hover:bg-gray-700 dark:hover:bg-gray-600 transition duration-300 uppercase text-sm sm:text-base">{{__('interface.select')}}</a>
    </div>

    <div class="mt-auto">
        <a href="{{$site->domain}}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">Weboldal megtekintése</a>
    </div>
</div>

