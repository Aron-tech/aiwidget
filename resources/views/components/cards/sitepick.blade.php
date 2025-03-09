@props(['site', 'auth_user'])
<div class="relative flex flex-col justify-between items-center col-span-1 sm:col-span-2
row-span-2 bg-white dark:bg-black/10 rounded-lg shadow-md w-full h-full p-6 hover:shadow-lg transition duration-300 border border-gray-200 dark:border-gray-700">
    <div class="absolute top-4 right-4 flex gap-2">
        @if($site->keys[0]->user_id=== $auth_user->id)
            <flux:button wire:click='edit({{$site->id}})' variant="filled">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                    <path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z" />
                    <path d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z" />
                </svg>
            </flux:button>
        @endif
        <flux:button wire:click='delete({{$site->id}})' variant="danger">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                <path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z" clip-rule="evenodd" />
              </svg>
        </flux:button>
    </div>

    <div class="flex flex-col justify-center items-center flex-grow">
        <x-text.h2>{{$site->name}}</x-text.h2>
        <a href="{{ route('dashboard', $site->uuid) }}" class="bg-gray-800 dark:bg-gray-700 text-white px-4 sm:px-6 py-2 rounded-lg font-medium hover:bg-gray-700 dark:hover:bg-gray-600 transition duration-300 uppercase text-sm sm:text-base">{{__('interface.select')}}</a>
    </div>

    <div class="mt-auto">
        <a href="{{$site->domain}}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">Weboldal megtekintése</a>
    </div>
</div>

