<?php

use Livewire\Volt\Component;
use App\Models\Site;
use Livewire\Attributes\On;
use App\Models\User;

new class extends Component {

    public ?User $auth_user = null;

    public ?Site $site = null;

    #[On('openDeleteModal')]
    public function openDeleteModal($site_id)
    {
        $this->site = Site::find($site_id);

        $this->auth_user = auth()->user();

        if(empty($this->site) || empty($this->auth_user))
        {
            return;
        }

        Flux::modal('delete-site')->show();
    }

    public function deleteSite()
    {
        $user_key = $this->auth_user->sites()->where('site_id', $this->site->id)->first()->keys[0];

        if($user_key->type === 1){

            $this->site->keys()
                ->where('type', 0)
                ->delete();

               $this->site->delete();

            $user_key->update([
                    'site_id' => null,
                    'user_id' => null,
                ]);
        }else if ($user_key->type === 0) {
            $user_key->delete();
        }

        Flux::modal('delete-site')->close();

        $this->dispatch('reloadSites');

        $this->dispatch('notify','success',__('interface.delete_success'));
    }
}; ?>

<div>
    <flux:modal name="delete-site" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{__('interface.site_delete_title')}}</flux:heading>
                <flux:subheading>
                    <p>{{__('interface.site_delete_message') . $site?->name}}</p>
                    <p class="font-medium">{{__('interface.irreversible')}}</p>
                </flux:subheading>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">{{__('interface.cancel')}}</flux:button>
                </flux:modal.close>

                <flux:button wire:click='deleteSite()' type="submit" variant="danger">{{__('interface.delete')}}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
