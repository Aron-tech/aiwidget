<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Site;
use Livewire\Attributes\On;
use Livewire\WithoutUrlPagination;
use App\Livewire\Traits\GlobalNotifyEvent;


class UserManager extends Component
{
    use WithPagination, WithoutUrlPagination, GlobalNotifyEvent;

    public $site;
    public $search = '';
    public $filter = 0; // 0: Összes, 1: Aktivált, 2: Nem aktivált

    public function mount(Site $site)
    {
        $this->site = $site;
    }

    public function create()
    {
        $this->dispatch('createKey', $this->site->id);
    }

    public function delete($key_id)
    {
        $this->dispatch('deleteKey', $key_id, $this->site->id);
    }

    #[On("reloadKeys")]
    public function reloadKeys()
    {
        $this->resetPage();
    }

    public function render()
    {
        $keys = $this->site->keys()
            ->with(['user' => function($query) {
                $query->select('id', 'name', 'email');
            }])
            ->where(function($query) {
                $query->where('token', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', function($query) {
                        $query->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%');
                    });
            })
            ->when($this->filter === '1', function($query) {
                $query->whereNotNull('user_id');
            })
            ->when($this->filter === '2', function($query) {
                $query->whereNull('user_id');
            })
            ->paginate(10);

        return view('livewire.user-manager', [
            'keys' => $keys,
        ]);
    }
}