<?php

use Livewire\Volt\Component;
use App\Models\Site;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use App\Enums\KeyTypesEnum;
use App\Enums\PermissionTypesEnum;
use App\Models\Key;

new class extends Component {

    public $token = '';

    public ?Site $site = null;

    public array $permissions = [];

    private function clearForm()
    {
        $this->token = '';
        $this->permissions = [];
    }

    #[On("createKey")]
    public function openCreateKeyModal($site_id)
    {
        $this->site = Site::find($site_id);

        $this->token = Str::random(40);

        if(empty($this->site))
            $this->dispatch('notify', 'warning', __('interface.missing_site'));
        else if(empty($this->token))
            $this->dispatch('notify', 'warning', __('interface.missing_token'));
        else if(auth()->user()->cannot('hasPermission', PermissionTypesEnum::CREATE_KEYS))
                return $this->dispatch('notify', 'danger', __('interface.missing_permission'));
        else
            Flux::modal('create-key')->show();
    }

    public function createKey()
    {
        if(empty($this->site))
            $this->dispatch('notify', 'warning', __('interface.missing_site'));

        $key = $this->site->keys()->create([
            'token' => $this->token,
            'expiration_time' => now()->addDays(360),
            'type' => KeyTypesEnum::MODERATOR,
        ]);

        $key->assignMultiplePermissions(
            collect($this->permissions)
                ->filter()
                ->keys()
                ->map(fn($permission) => PermissionTypesEnum::from($permission))
                ->toArray()
        );

        Flux::modal('create-key')->close();

        $this->dispatch('notify', 'success', __('interface.create_success'));

        $this->dispatch('reloadKeys');

        $this->clearForm();
    }
}; ?>

<div>
    <flux:modal name="create-key" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{__('interface.add_new_key')}}</flux:heading>
                <flux:subheading>{{__('interface.add_new_key_subheading')}}</flux:subheading>
            </div>

            <div class="mt-4">
                <flux:input wire:model="token" icon="key" id="token" label="{{ __('interface.token') }}" type="token" name="token" required autocomplete="token" placeholder="key-token" readonly copyable />
            </div>

            <flux:fieldset>
                <flux:legend>{{ __('interface.permissions') }}</flux:legend>

                <div class="space-y-4">
                    @foreach (App\Enums\PermissionTypesEnum::cases() as $permission)
                        <flux:switch wire:model="permissions.{{ $permission->value }}" label="{{ $permission->getLabel() }}"/>
                    @endforeach
                </div>
            </flux:fieldset>

            <div class="flex">
                <flux:spacer />
                <flux:button type="submit" wire:click='createKey()' variant="primary">{{__('interface.create_key')}}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
