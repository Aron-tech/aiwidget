<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Traits\GlobalNotifyEvent;

class SitePicker extends Component
{
    use GlobalNotifyEvent;

    public $auth_user = null;

    public $auth_key = null;

    public $sites = null;

    public $site = null;

    private function getSitesWithOwner()
    {
        return $this->auth_user->sites()
            ->select('sites.id','sites.uuid', 'sites.domain', 'sites.name')
            ->with(['keys' => function ($query) {
            $query->where('type', 1)->select('user_id', 'site_id');
        }])->get();
    }

    public function edit($site_id)
    {
        $this->dispatch('editSite', $site_id);
    }

    public function delete($site_id)
    {
        $this->dispatch('openDeleteModal', $site_id);
    }

    public function mount()
    {
        $this->auth_user = Auth::user();
        $this->sites = $this->getSitesWithOwner();
    }

    #[On("reloadSites")]
    public function reloadSites()
    {
        $this->sites = $this->getSitesWithOwner();
    }

    public function render()
    {
        return view('livewire.site-picker');
    }
}
