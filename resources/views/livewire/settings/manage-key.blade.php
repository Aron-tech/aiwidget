<?php

use App\Models\SiteSelector;
use Livewire\Volt\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

new class extends Component {
    public string $password = '';
    public string $entered_key = '';
    public string $new_key = '';

    public function mount()
    {
        $this->generateKey();
    }

    public function generateKey()
    {
        $this->new_key = Str::uuid()->toString();
    }

    public function updateToken()
    {
        $user = auth()->user();
        $site = SiteSelector::getSite();

        if (!Hash::check($this->password, $user->password)) {
            session()->flash('error', __('auth.password'));
            return;
        }
        $key_record = $user->keys()
            ->where('site_id', $site->id)
            ->where('token', hash('sha256', $this->entered_key))
            ->first();

        if (!$key_record) {
            session()->flash('error', __('interface.invalid_key_for_site'));
            return;
        }

        $key_record->update(['token' => $this->new_key]);

        session()->flash('success', __('interface.key_regenerated_success'));
    }
}; ?>
<div class="flex flex-col items-start">
    @include('partials.settings-heading')
    <x-notification.panel :notifications="session()->all()"/>
    <x-settings.layout heading="{{ __('interface.manage_key') }}" subheading="{{ __('interface.manage_your_key') }}">

        <div class="space-y-4">
            <flux:input
                label="{{ __('interface.password') }}"
                type="password"
                wire:model.defer="password"
            />

            <flux:input
                label="{{ __('interface.current_key') }}"
                type="text"
                wire:model.defer="entered_key"
            />

            <div class="flex flex-wrap items-center gap-4">
                @if ($new_key)
                    <flux:input copyable
                                label="{{ __('interface.new_key') }}"
                                readonly
                                value="{{ $new_key }}"
                    />
                @endif

                <flux:button class="mt-6" wire:click="generateKey()" icon="arrow-path"></flux:button>
            </div>

            <flux:button wire:click="updateToken()" variant="primary">@lang('interface.change')</flux:button>
        </div>

    </x-settings.layout>
</div>
