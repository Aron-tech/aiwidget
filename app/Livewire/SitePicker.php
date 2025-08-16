<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Traits\GlobalNotifyEvent;
use App\Models\Site;
use App\Models\SiteSelector;
use App\Enums\KeyTypesEnum;

class SitePicker extends Component
{
    use GlobalNotifyEvent;

    public $auth_user = null;

    public $auth_key = null;

    public $sites = null;

    private function getSitesWithOwner()
    {
        return $this->auth_user->sites()
            ->select('sites.id','sites.uuid', 'sites.domain', 'sites.name')
            ->with(['keys' => function ($query) {
            $query->where('type', KeyTypesEnum::OWNER)->select('user_id', 'site_id');
        }])->get();
    }

    public function select($site_id): void
    {
        $auth_user_key = $this->auth_user->keys()->where('site_id', $site_id)->first();

        //Ha nincs token, akkor nem engedjük tovább
        if(!$auth_user_key) {
            $this->notify('danger', __('interface.invalid_token'));
            return;
        }

        //Ha több mint 3 napja lejárt a token, akkor nem engedjük tovább
        if($auth_user_key->expiration_time <= now()->subDays(3)) {
            if($auth_user_key->type === KeyTypesEnum::OWNER) {
                $this->notify('danger', __('interface.invalid_token'));
            }else {
                $this->notify('warning', __('interface.invalid_token_contact_owner'));
            }
            return;
        }

        $site = Site::findOrFail($site_id);
        $site_selector = new SiteSelector();
        $site_selector->setSite($site);

        redirect()->route('dashboard');
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
