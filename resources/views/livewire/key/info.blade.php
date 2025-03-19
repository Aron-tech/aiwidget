<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div>
    <flux:modal name="info-modal" class="min-w-[22rem]">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('interface.info') }}</flux:heading>

            <flux:subheading>
                <p>{{ __('interface.token_enscrypted_info') }}</p>
                <flux:separator class="my-4" />
                <p>{{ __('interface.token_create_info') }}</p>
            </flux:subheading>
        </div>
    </div>
</flux:modal>
</div>
