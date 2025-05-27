<?php

namespace App\Livewire;

use App\Enums\ChatStatusEnum;
use App\Enums\MessageSenderRolesEnum;
use App\Livewire\Traits\GlobalNotifyEvent;
use App\Models\Chat;
use App\Models\Message;
use App\Models\Site;
use App\Models\SiteSelector;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class ChatManager extends Component
{
    use WithPagination;
    use GlobalNotifyEvent;

    public ?Site $site = null;
    public ?Chat $selected_chat = null;
    public string $new_message = '';
    public string $search = '';

    public int $filter = 0;
    public int $order_by = 0;
    public string $order = 'desc';

    protected $listeners = ['refreshChats' => '$refresh'];

    public function mount(SiteSelector $site_selector): void
    {
        $this->site = $site_selector->getSite();
    }

    public function toggleOrderDirection(): void
    {
        $this->order = $this->order === 'asc' ? 'desc' : 'asc';
        $this->resetPage();
    }

    private function getFilter(): array
    {
        return match ($this->filter) {
            0 => [ChatStatusEnum::OPEN, ChatStatusEnum::WAITING],
            1 => [ChatStatusEnum::OPEN],
            2 => [ChatStatusEnum::WAITING],
            default => null,
        };
    }

    public function closeChat(Chat $chat): void
    {
        $this->dispatch('closeChat', $chat->id);
    }

    private function getChatQuery(): Builder
    {
        $query = Chat::where('site_id', $this->site->id)
            ->whereIn('status', $this->getFilter())
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('visitor_name', 'like', '%'.$this->search.'%')
                        ->orWhere('visitor_email', 'like', '%'.$this->search.'%');
                });
            });

        return match ($this->order_by) {
            0 => $query->orderBy('created_at', $this->order),
            1 => $query->orderBy('updated_at', $this->order),
            2 => $query->orderBy('status'),
            3 => $query->orderBy('id', $this->order),
            default => $query->latest(),
        };
    }

    public function selectChat(int $chat_id): void
    {
        $this->selected_chat = Chat::with(['messages' => function($query) {
            $query->orderBy('created_at', 'asc');
        }])->find($chat_id);

        $this->dispatch('chatSelected');
    }

    public function getMessagesProperty()
    {
        if (!$this->selected_chat) {
            return collect();
        }

        return $this->selected_chat->messages;
    }

    public function sendMessage(): void
    {
        if (empty($this->new_message) || !$this->selected_chat) {
            return;
        }

        Message::create([
            'chat_id' => $this->selected_chat->id,
            'message' => $this->new_message,
            'sender_role' => MessageSenderRolesEnum::ADMIN,
        ]);

        $this->new_message = '';
        $this->dispatch('messageSent');
    }

    public function toggleChatStatus(int $chat_id): void
    {
        $chat = Chat::find($chat_id);

        if ($chat) {
            $chat->status = $chat->status === ChatStatusEnum::OPEN
                ? ChatStatusEnum::CLOSED
                : ChatStatusEnum::OPEN;
            $chat->save();

            $this->dispatch('refreshChats');
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    #[On('reloadChats')]
    public function reloadChats(): void
    {
        $this->dispatch('refreshChats');
    }

    public function updatedOrderBy(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.chat-manager', [
            'chats' => $this->getChatQuery()->paginate(10),
            'selected_chat' => $this->selected_chat,
            'messages' => $this->messages,
        ]);
    }
}
