<?php

use Livewire\Volt\Component;
use App\Models\Key;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

new class extends Component {

    public $auth_user = null;

    public $new_site_name = '';
    public $new_site_domain = '';
    public $token = null;

    public $key = null;

    public function addWebsite()
    {
        $validated = $this->validate([
            'token' => 'required',
        ]);

        $hashed_token = hash('sha256', $validated['token']);

        $this->key = Key::where('token', $hashed_token)
            ->where('user_id', null)
            ->first();

        if(empty($this->key))
        {
            $this->addError('token', 'Érvénytelen token.');
            return;
        }

        if($this->key->type === 1)
        {
            Flux::modal('create-site')->show();
        }else if($this->key->type === 2 || $this->key->type === 0){
            $this->key->update([
                'user_id' => $this->auth_user->id,
                ]);

            $this->dispatch('reloadSites');
        }

        Flux::modal('add-site')->close();

        $this->resetForm();
    }

    public function createSite()
    {
        $validated = $this->validate([
            'new_site_name' => 'required',
            'new_site_domain' => 'required|url|unique:sites,domain',
        ]);

        $site = $this->key->site()->create([
                'name' => $validated['new_site_name'],
                'domain' => $validated['new_site_domain'],
            ]);

        $this->key->update([
                'site_id' => $site->id,
                'user_id' => $this->auth_user->id,
            ]);

        $this->resetForm();

        Flux::modal('create-site')->close();

        $this->dispatch('reloadSites');

    }

    private function resetForm()
    {
        $this->new_site_name = '';
        $this->new_site_domain = '';
        $this->token = '';
    }

    public function mount()
    {
        $this->auth_user = Auth::User();
    }
};
?>
<div>
    <flux:modal name="add-site" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{__('interface.add_new_website')}}</flux:heading>
                <flux:subheading>{{__('interface.add_new_website_subheading')}}</flux:subheading>
            </div>

            <div class="mt-4">
                <flux:input wire:model="token" icon="key" id="token" label="{{ __('interface.token') }}" type="token" name="token" required autocomplete="token" placeholder="key-token" clearable/>
            </div>

            <div class="flex">
                <flux:spacer />
                <flux:button type="submit" wire:click='addWebsite()' variant="primary">{{__('interface.add_site')}}</flux:button>
            </div>
        </div>
    </flux:modal>


    <flux:modal name="create-site" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{__('interface.add_new_website')}}</flux:heading>
                <flux:subheading>{{__('interface.add_new_website_subheading')}}</flux:subheading>
            </div>

            <div class="mt-4">
                <flux:input wire:model="new_site_name" id="new_site_name" label="{{ __('interface.site_name') }}" type="text" name="new_site_name" required autofocus autocomplete="new_site_name" placeholder="My website name" clearable />
            </div>

            <div class="mt-4">
                <flux:input wire:model="new_site_domain" icon="link" id="new_site_domain" label="{{ __('interface.domain') }}" type="url" name="new_site_domain" placeholder="https://mywebsite.hu/" clearable/>
            </div>

            <div class="flex">
                <flux:spacer />
                <flux:button type="submit" wire:click='createSite()' variant="primary">{{__('interface.add_site')}}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
