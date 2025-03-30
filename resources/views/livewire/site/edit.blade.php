<?php

use Livewire\Volt\Component;
use App\Models\Site;
use Livewire\Attributes\On;
use App\Enums\KeyTypesEnum;

new class extends Component {

    public ?Site $site = null;

    public $new_site_name = '';
    public $new_site_domain = '';


    #[On("editSite")]
    public function editSite($site_id)
    {

        $this->site = Site::find($site_id);

        if(empty($this->site))
        {
            $this->dispatch('notify','warning',__('interface.missing_site'));
        }

        $owner_key_exists = $this->site->keys()->where('user_id', auth()->user()->id)->where('type', KeyTypesEnum::OWNER)->exists();

        if(!$owner_key_exists)
        {
            $this->dispatch('notify','danger',__('interface.missing_permission'));
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
            $this->dispatch('notify','danger',__('interface.missing_site'));
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

        $this->dispatch('notify','success',__('interface.edit_success'));
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

            <flux:button wire:click='updateSite' type="submit" variant="primary">{{__('interface.save_changes')}}</flux:button>
        </div>
    </div>
</flux:modal>
</div>
