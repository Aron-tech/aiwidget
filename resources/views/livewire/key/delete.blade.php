<?php

use Livewire\Volt\Component;
use App\Models\Key;
use Livewire\Attributes\On;
use App\Enums\KeyTypesEnum;
use App\Enums\PermissionTypesEnum;

new class extends Component {

    public ?Key $key = null;

    public $site_id = null;

    #[On('deleteKey')]
    public function delete($key_id, $site_id)
    {
        $this->key = Key::findOrFail($key_id);
        $this->site_id = $site_id;

        if(auth()->user()->cannot(PermissionTypesEnum::DELETE_KEYS))
            return $this->dispatch('notify', 'danger', __('interface.missing_permission'));

        Flux::modal('delete-key')->show();
    }

    public function destroy()
    {
        if(auth()->user()->keys()->where('site_id', $this->site_id)->select('type')->first()->type !== KeyTypesEnum::MODERATOR){
                $this->key->permissions()->detach();
                $this->key->delete();
                $this->dispatch('notify','success', __('interface.delete_success'));
        }
        else {
            $this->dispatch('notify', 'danger', __('interface.delete_fail'));
        }

        Flux::modal('delete-key')->close();
        $this->dispatch('reloadKeys');

        $this->site_id = null;
        $this->key = null;
    }
}; ?>

<div>
    <flux:modal name="delete-key" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{__('interface.key_delete_title')}}</flux:heading>
                <flux:subheading>
                    <p>{{__('interface.key_delete_message') . $key?->user?->name}}</p>
                    <p class="font-medium">{{__('interface.irreversible')}}</p>
                </flux:subheading>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">{{__('interface.cancel')}}</flux:button>
                </flux:modal.close>

                <flux:button wire:click='destroy()' type="submit" variant="danger">{{__('interface.delete')}}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
