<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Site;
use Livewire\Attributes\On;
use Livewire\WithoutUrlPagination;
use App\Livewire\Traits\GlobalNotifyEvent;
use App\Models\SiteSelector;
use App\Enums\KeyTypesEnum;

class UserManager extends Component
{
    use WithPagination, WithoutUrlPagination, GlobalNotifyEvent;

    public ?Site $site;
    public $search = '';
    public $filter = 0; // 0: Összes, 1: Aktivált, 2: Nem aktivált

    public function mount(SiteSelector $site_selector)
    {
        if (!$site_selector->hasSite()) {
            return redirect()->route('site.picker')->with('error', __('interface.missing_site'));
        }

        $this->site = $site_selector->getSite();
    }

    public function create()
    {
        $this->dispatch('createKey', $this->site->id);
    }

    public function edit($key_id)
    {
        $this->dispatch('editKey', $key_id, $this->site->id);
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

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $keys = $this->site->keys()
            ->with(['user' => function($query) {
                $query->select('id', 'name', 'email');
            }])
            ->where('keys.type', KeyTypesEnum::MODERATOR)
            ->when($this->search, function($query) {
                $query->where(function ($query) {
                    $query->search($this->search);
                })->orWhereHas('user', function ($query) {
                    $query->search($this->search);
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