<?php

use Livewire\Volt\Component;
use App\Models\Site;
use App\Models\Key;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use App\Livewire\Traits\RequiresPermission;
use App\Enums\PermissionTypesEnum;



new class extends Component {

    use RequiresPermission;

    public ?Site $site = null;

    public ?array $permissions = [];

    public ?Key $key = null;

    #[On("editKey")]
    public function editKey($key_id, $site_id)
    {
        if(!$this->hasPermission(PermissionTypesEnum::EDIT_KEYS))
            return;

        $this->key = Site::find($site_id)->keys()->with('permissions')->findOrFail($key_id);

        $current_permissions = $this->key->permissions->pluck('value')->toArray();

        $all_permissions = PermissionTypesEnum::cases();

        $this->permissions = array_reduce(
            PermissionTypesEnum::cases(),
            fn($carry, $p) => $carry + [$p->value => in_array($p->value, $current_permissions)],
            []
        );


        Flux::modal('edit-key')->show();
    }

    public function updateKey()
    {
        if(empty($this->key))
            return $this->dispatch('notify', 'warning', __('interface.missing_key'));

        $this->key->permissions()->detach();

        $this->key->assignMultiplePermissions(
            collect($this->permissions)
                ->filter()
                ->keys()
                ->map(fn($permission) => PermissionTypesEnum::from($permission))
                ->toArray()
        );

        Flux::modal('edit-key')->close();

        $this->dispatch('reloadKeys');

        $this->dispatch('notify', 'success', __('interface.edit_success'));
    }
};
?>

<div>
    <flux:modal name="edit-key" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{__('interface.edit_key')}}</flux:heading>
                <flux:subheading>{{ __('interface.key_subheading')}}</flux:subheading>
            </div>

            @foreach ($permissions as $key => $value)
                <flux:switch wire:model="permissions.{{ $key }}" label="{{ PermissionTypesEnum::from($key)->getLabel() }}"/>
            @endforeach

            <div class="flex">
                <flux:spacer />

                <flux:button wire:click='updateKey()' type="submit" variant="primary">{{ __('interface.save') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
