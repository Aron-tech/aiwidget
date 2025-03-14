<?php

use Livewire\Volt\Component;
use App\Models\Site;
use Livewire\Attributes\On;

new class extends Component {

    public $site = null;

    public $new_site_name = '';
    public $new_site_domain = '';


    #[On("editSite")]
    public function editSite($site_id)
    {

        $this->site = Site::find($site_id);

        if(empty($this->site))
        {
            return;
        }

        $this->new_site_name = $this->site->name;
        $this->new_site_domain = $this->site->domain;

        Flux::modal('edit-site')->show();
    }

    public function updateSite()
    {
        if(empty($this->site))
        {
            return;
        }

        $validated = $this->validate([
            'new_site_name' => 'required',
            'new_site_domain' => 'required|url|unique:sites,domain,' . $this->site->id,
        ]);

        $this->site->update([
            'name' => $validated['new_site_name'],
            'domain' => $validated['new_site_domain'],
        ]);

        Flux::modal('edit-site')->close();

        $this->dispatch('reloadSites');
    }

}; ?>

<div>
<flux:modal name="edit-site" class="md:w-96">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{__('interface.site_update')}}</flux:heading>
            <flux:subheading>{{__('interface.site_update_subheading')}}</flux:subheading>
        </div>

        <div class="mt-4">
            <flux:input wire:model="new_site_name" id="new_site_name" label="{{ __('interface.site_name') }}" type="text" name="new_site_name" required autofocus autocomplete="new_site_name" placeholder="My website name" clearable/>
        </div>

        <div class="mt-4">
            <flux:input wire:model="new_site_domain" icon="link" id="new_site_domain" label="{{ __('interface.domain') }}" type="url" name="new_site_domain" placeholder="https://mywebsite.hu/" clearable/>
        </div>

        <div class="flex">
            <flux:spacer />

            <flux:button wire:click='updateSite' type="submit" variant="primary">Save changes</flux:button>
        </div>
    </div>
</flux:modal>
</div>
