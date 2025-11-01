<div class="dark:text-gray-100 min-h-screen">
    <x-notification.panel :notifications="session()->all()"/>
    <div class="sm:block hidden mb-4">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{route('dashboard')}}" icon="home" />
            <flux:breadcrumbs.item>{{__('interface.chat_manager')}}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </div>

    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 mb-4 justify-between sm:justify-end items-start sm:items-center">
        <div class="flex w-full sm:w-auto" x-data @keydown.window.prevent.meta.k="$refs.searchInput.focus()" @keydown.window.prevent.ctrl.k="$refs.searchInput.focus()">
            <flux:input
                wire:model.live='search'
                x-ref="searchInput"
                kbd="⌘K"
                icon="magnifying-glass"
                placeholder="{{ __('interface.search') }}"
                class="w-full sm:w-64  dark:border-gray-700 dark:text-white"
            />
        </div>
        <div class="flex space-x-2">
            <flux:dropdown>
                <flux:button icon-trailing="funnel"></flux:button>
                <flux:menu>
                    <flux:menu.radio.group wire:model.live='filter'>
                        <flux:menu.radio value="0">{{ __('interface.active')}}</flux:menu.radio>
                        <flux:menu.radio value="1">{{ __('interface.opened')}}</flux:menu.radio>
                        <flux:menu.radio value="2">{{ __('interface.waiting')}}</flux:menu.radio>
                        <flux:menu.radio value="3">{{ __('interface.closed')}}</flux:menu.radio>
                    </flux:menu.radio.group>
                </flux:menu>
            </flux:dropdown>

            <flux:dropdown>
                <flux:button icon-trailing="adjustments-horizontal"></flux:button>
                <flux:menu>
                    <flux:menu.radio.group wire:model.live='order_by'>
                        <flux:menu.radio value="0" wire:click="toggleOrderDirection">
                            {{ __('interface.sort_by_created') }}
                            @if($order_by === 0) ({{ strtoupper($order) }}) @endif
                        </flux:menu.radio>
                        <flux:menu.radio value="1" wire:click="toggleOrderDirection">
                            {{ __('interface.sort_by_updated') }}
                            @if($order_by === 1) ({{ strtoupper($order) }}) @endif
                        </flux:menu.radio>
                        <flux:menu.radio value="2">
                            {{ __('interface.sort_by_status') }}
                        </flux:menu.radio>
                        <flux:menu.radio value="3" wire:click="toggleOrderDirection">
                            {{ __('interface.sort_by_id') }}
                            @if($order_by === 3) ({{ strtoupper($order) }}) @endif
                        </flux:menu.radio>
                    </flux:menu.radio.group>
                </flux:menu>
            </flux:dropdown>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-4">
        <div class="w-full lg:w-2/3 order-2 lg:order-1">
            @if($selected_chat)
                <div class="border-b px-4 py-3 flex items-center justify-between rounded-t-lg">
                    <div>
                        <h3 class="text-lg font-medium dark:text-white">#{{$selected_chat->id}} - {{ $selected_chat->visitor_name }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-300">{{ $selected_chat->visitor_email }}</p>
                    </div>
                    @if($selected_chat->status === \App\Enums\ChatStatusEnum::OPEN || $selected_chat->status === \App\Enums\ChatStatusEnum::WAITING)
                        <div class="flex space-x-2">
                            <flux:tooltip content="{{__('interface.close_chat')}}">
                                <flux:button icon="lock-closed" wire:click="closeChat({{$selected_chat->id}})"/>
                            </flux:tooltip>
                        </div>
                    @endif
                </div>

                <div class="h-132 overflow-y-auto p-4 space-y-4" id="messages-container">
                    @foreach($messages as $message)
                        <div class="flex {{ $message->sender_role === \App\Enums\MessageSenderRolesEnum::USER ? 'justify-start' : 'justify-end' }}">
                            <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg {{$message->sender_role === \App\Enums\MessageSenderRolesEnum::USER ? 'bg-gray-500' : 'bg-blue-500' }}">
                                <flux:text class="text-white text-base">{{$message->message}}</flux:text>
                                <flux:text class="text-white text-xs">{{$message->created_at}}</flux:text>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-3">
                    <div class="flex space-x-2">
                        <form wire:submit.prevent="sendMessage" class="flex space-x-4 items-center w-full">
                            <flux:input wire:model="new_message" placeholder="{{__('interface.message')}}" clearable />
                            @if($selected_chat->status === \App\Enums\ChatStatusEnum::OPEN || $selected_chat->status === \App\Enums\ChatStatusEnum::WAITING)
                                <flux:button icon="paper-airplane" type="submit"/>
                            @endif
                        </form>
                    </div>
                </div>
            @else
                <div class="h-96 flex flex-col items-center justify-center text-gray-400 dark:text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <p class="mt-2">{{ __('interface.select_chat_to_start') }}</p>
                </div>
            @endif
        </div>

        <div class="w-full lg:w-1/3 order-1 lg:order-2">
            <flux:navlist>
                <flux:navlist.group heading="{{__('interface.chat')}}" class="mt-4 dark:text-white">
                    @foreach($chats as $chat)
                        <flux:navlist.item
                            wire:click="selectChat({{ $chat->id }})"
                            class="dark:hover:bg-gray-700 dark:text-gray-200 py-6 px-4 {{$selected_chat?->id === $chat->id ? 'dark:bg-black/10 bg-gray-100' : ''}}"
                        >
                            <div class="flex items-center justify-between w-full">
                                <div>
                                    <p class="font-medium dark:text-white">{{ $chat->visitor_name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $chat->visitor_email }}</p>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                    {{ $chat->status->getLabel() }}
                                </span>
                            </div>
                        </flux:navlist.item>
                    @endforeach
                </flux:navlist.group>
                {{$chats->links()}}
            </flux:navlist>
        </div>
    </div>
    @livewire('chat.close')
</div>
