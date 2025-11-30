<?php

use App\Enums\ChatStatusEnum;
use App\Enums\PermissionTypesEnum;
use Livewire\Volt\Component;
use App\Models\Chat;
use Livewire\Attributes\On;

new class extends Component {

    public ?Chat $chat = null;

    #[On('closeChat')]
    public function showCloseChatModal($chat_id)
    {
        $this->chat = Chat::query()
            ->where('id', $chat_id)
            ->whereIn('status', [ChatStatusEnum::OPEN, ChatStatusEnum::WAITING])
            ->first();

        if (auth()->user()->cannot('hasPermission', PermissionTypesEnum::CLOSE_CHAT))
            return $this->dispatch('notify', 'danger', __('interface.missing_permission'));

        Flux::modal('close-chat')->show();
    }

    public function close()
    {
        $this->chat->update(['status' => ChatStatusEnum::CLOSED]);

        $this->dispatch('notify', 'success', __('interface.closed_success'));

        Flux::modal('close-chat')->close();

        $this->dispatch('reloadChats');
    }
}; ?>

<div>
    <flux:modal name="close-chat" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{__('interface.close_chat')}}</flux:heading>

                <flux:text class="mt-2">
                    <p>{{__('interface.close_chat_content')}}</p>
                    <p>{{__('interface.irreversible')}}</p>
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer/>

                <flux:modal.close>
                    <flux:button variant="ghost">{{__('interface.cancel')}}</flux:button>
                </flux:modal.close>

                <flux:button wire:click="close" type="submit" variant="primary">{{__('interface.close')}}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
