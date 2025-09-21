<?php

use App\Actions\CreateStripeCheckout;
use App\Enums\SystemUsageFeePeriodEnum;
use Livewire\Volt\Component;
use App\Models\Key;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;
use App\Models\User;
use App\Enums\KeyTypesEnum;
use Livewire\Attributes\Validate;

new class extends Component {

    public ?User $auth_user = null;

    #[Validate('required|min:3|max:255|string')]
    public ?string $new_site_name = null;
    #[Validate('required|url|unique:sites,domain|max:255')]
    public ?string $new_site_domain = null;

    public ?string $token = null;

    public ?Key $key = null;

    public ?SystemUsageFeePeriodEnum $selected_fee_period = SystemUsageFeePeriodEnum::MONTHLY;
    public float $upload_amount = 5;
    public float $total_amount = 0;
    public string $formatted_total_amount;

    public function updatedUploadAmount()
    {
        $this->total_amount = round(($this->upload_amount ?? 0) + ($this->selected_fee_period?->getFee() ?? 0), 2);
        $this->formatted_total_amount = $this->getFormattedTotalAmountProperty();
    }

    public function updatedSelectedFeePeriod()
    {
        $this->total_amount = round(($this->upload_amount ?? 0) + ($this->selected_fee_period?->getFee() ?? 0), 2);
        $this->formatted_total_amount = $this->getFormattedTotalAmountProperty();
    }

    public function getFormattedTotalAmountProperty()
    {
        return $this->total_amount ? number_format($this->total_amount, 2) . ' €' : '';
    }

    public function checkOut(): void
    {
        $this->validate([
            'upload_amount' => 'required|numeric|min:5',
        ]);

        $total_in_cents = $this->total_amount * 100;
        $session = CreateStripeCheckout::run($total_in_cents, product_name: __('interface.buy_token_product'), metadata: array(
            'user_id' => $this->auth_user->id,
            'days_number' => $this->selected_fee_period->toDays(),
            'note' => __('interface.buy_token_product'),
            'fee_period' => $this->selected_fee_period->getFee(),
        ));

        redirect($session->url);
    }

    public function addWebsite()
    {
        $validated = $this->validate([
            'token' => 'required|string|min:8|max:64',
        ]);

        $hashed_token = hash('sha256', $validated['token']);

        $this->key = Key::where('token', $hashed_token)
            ->where('user_id', null)
            ->where('expiration_time', '>', now())
            ->first();

        if (empty($this->key)) {
            $this->addError('token', __('interface.invalid_token'));
            return;
        }

        if ($this->key->type === KeyTypesEnum::OWNER) {
            Flux::modal('create-site')->show();

        } else if ($this->key->type === KeyTypesEnum::MODERATOR || $this->key->type === KeyTypesEnum::DEVELOPER) {

            if (Key::where('user_id', $this->auth_user->id)->where('site_id', $this->key->site_id)->exists()) {
                $this->addError('token', __('interface.attached_to_the_website'));
                return;
            }

            $this->key->update([
                'user_id' => $this->auth_user->id,
            ]);

            $this->dispatch('reloadSites');

            $this->dispatch('notify', 'success', __('interface.add_success'));
        }

        Flux::modal('add-site')->close();

        $this->resetForm();
    }

    public function createSite()
    {
        $validated = $this->validate();

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

        $this->dispatch('notify', 'success', __('interface.create_success'));
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
        $this->total_amount = round($this->upload_amount + ($this->selected_fee_period?->getFee() ?? 0), 2);
        $this->formatted_total_amount = $this->getFormattedTotalAmountProperty();
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
                <flux:input wire:model="token" icon="key" id="token" label="{{ __('interface.token') }}" type="token"
                            name="token" required autocomplete="token" placeholder="key-token" clearable/>
            </div>

            <div class="flex justify-between">
                <flux:modal.trigger name="buy-token">
                    <flux:button x-on:click="$flux.modal('add-site').close()">Buy token</flux:button>
                </flux:modal.trigger>
                <flux:button type="submit" wire:click='addWebsite()'
                             variant="primary">{{__('interface.add_site')}}</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="buy-token" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{__('interface.purchase_token')}}</flux:heading>
                <flux:subheading>{{__('interface.buy_token_subheading')}}</flux:subheading>
            </div>
            <div class="space-y-4">
                <flux:select wire:model.live.debounce="selected_fee_period" label="{{__('interface.system_usage_subscription')}}">
                    @foreach(SystemUsageFeePeriodEnum::cases() as $fee_period_enum)
                        <flux:select.option value="{{ $fee_period_enum->value }}">
                            {{ $fee_period_enum->label() }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
                <flux:input label="{{ __('interface.top_up_amount') }}" type="number" min="5" wire:model.live="upload_amount"/>
                <flux:input
                    label="{{ __('interface.total_amount') }}"
                    wire:model.live="formatted_total_amount"
                    readonly
                />
                <flux:button wire:click="checkOut" icon="credit-card">{{__('interface.buy_now')}}</flux:button>
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
                <flux:input wire:model="new_site_name" id="new_site_name" label="{{ __('interface.site_name') }}"
                            type="text" name="new_site_name" required autofocus autocomplete="new_site_name"
                            placeholder="My website name" clearable/>
            </div>

            <div class="mt-4">
                <flux:input wire:model="new_site_domain" icon="link" id="new_site_domain"
                            label="{{ __('interface.domain') }}" type="url" name="new_site_domain"
                            placeholder="https://mywebsite.hu/" clearable/>
            </div>

            <div class="flex">
                <flux:spacer/>
                <flux:button type="submit" wire:click='createSite()'
                             variant="primary">{{__('interface.add_site')}}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
