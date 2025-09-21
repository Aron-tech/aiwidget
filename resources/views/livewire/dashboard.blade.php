<?php

use App\Actions\CreateStripeCheckout;
use App\Enums\BalanceTransactionTypeEnum;
use App\Enums\KeyTypesEnum;
use App\Livewire\Traits\GlobalNotifyEvent;
use App\Models\Balance;
use App\Models\Key;
use App\Models\Site;
use App\Models\SiteSelector;
use Carbon\Carbon;
use Livewire\Volt\Component;
use App\Enums\SystemUsageFeePeriodEnum;

new class extends Component {

    use GlobalNotifyEvent;

    public ?Site $site = null;
    public float $balance = 0;
    public ?Key $ownerKey = null;

    public float $upload_amount = 0;

    public SystemUsageFeePeriodEnum $selected_period = SystemUsageFeePeriodEnum::MONTHLY;

    public function mount(SiteSelector $site_selector): void
    {
        if (!$site_selector->hasSite()) {
            redirect()->route('site.picker')->with('error', __('interface.missing_site'));
            return;
        }

        $this->site = $site_selector->getSite();

        $this->balance = $this->getBalance();

        $this->last_transactions = $this->getLastTransactions();

        $this->ownerKey = $this->site->keys()->where('keys.type', KeyTypesEnum::OWNER)->first();
    }

    public function getBalance(): float
    {
        return $this->site->balances()->where('balances.type', BalanceTransactionTypeEnum::DEPOSIT)->sum('amount') - $this->site->balances()->whereIn('balances.type', [BalanceTransactionTypeEnum::PURCHASE, BalanceTransactionTypeEnum::RECURRING])->sum('amount');
    }

    public function getLastTransactions(int $amount = 10)
    {
        return $this->site->balances()->latest()->take($amount)->with('user')->get();
    }

    public function uploadAmount(): void
    {
        $this->validate([
            'upload_amount' => ['required', 'min:0.5', 'numeric'],
        ]);

        $total_in_cents = $this->upload_amount * 100;
        $session = CreateStripeCheckout::run($total_in_cents, product_name: __('interface.top_up_amount'), metadata: [
            'user_id' => auth()->id(),
            'key_id' => auth()->user()->keys()->where('site_id', $this->site->id)->first()->id,
            'note' => __('interface.top_up_amount')
        ]);

        redirect($session->url);
    }

    public function extendSubscription(): void
    {
        $this->validate([
            'selected_period' => ['required'],
        ]);

        try {
            if ($this->balance >= $this->selected_period->getFee()) {
                DB::transaction(function () {
                    Balance::create([
                        'user_id' => auth()->id(),
                        'key_id' => $this->ownerKey->id,
                        'amount' => $this->selected_period->getFee(),
                        'type' => BalanceTransactionTypeEnum::PURCHASE,
                        'description' => __('interface.extend_subscription'),
                    ]);

                    $this->ownerKey->expiration_time = Carbon::parse($this->ownerKey->expiration_time)->addDays($this->selected_period->toDays());
                    $this->ownerKey->save();

                    $this->balance = $this->balance - $this->selected_period->getFee();
                    $this->last_transactions = $this->getLastTransactions();
                    $this->notify('success', __('interface_extend_subscription_success'));
                });
            } else {
                $this->notify('danger', __('interface_extend_subscription_failed_no_'));
            }
        } catch (\Throwable $e) {
            $this->notify('error', __('interface.extend_subscription_failed_error'));
        }
    }

}; ?>
<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <x-notification.panel :notifications="session()->all()"/>
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">

        </div>
        <div
            class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">

        </div>
        <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">

        </div>
    </div>
    <div
        class="grid lg:grid-cols-[2fr_1fr_3fr] relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 lg:p-10 gap-4 space-y-2">
        <div class="space-y-2 lg:space-y-4">
            <div class="flex flex-col lg:flex-row justify-between lg:space-y-6">
                <flux:text
                    class="text-lg lg:text-2xl dark:text-white text-black">{{ __('interface.balance') }}</flux:text>
                <flux:text class="text-base lg:text-xl font-semibold">
                    {{ $this->balance }} €
                </flux:text>
            </div>
            <div class="space-y-2 lg:space-y-4">
                <flux:input wire:model="upload_amount" type="number" label="{{__('interface.top_up_amount')}}"/>
                <div class="w-full justify-end flex">
                    <flux:button wire:click="uploadAmount" class="space-x-2 sm:space-x-0 self-end">{{__('interface.pay')}}</flux:button>
                </div>
            </div>

            <flux:separator/>
            <flux:text class="text-lg lg:text-2xl dark:text-white text-black">Rendszer használati</flux:text>
            <flux:text>{{date("Y. M. d.", strtotime($this->ownerKey->expiration_time))}}</flux:text>
            <flux:select wire:model="selected_period"
                         label="{{__('interface.system_usage_subscription')}}">
                @foreach(SystemUsageFeePeriodEnum::cases() as $fee_period_enum)
                    <flux:select.option value="{{ $fee_period_enum->value }}">
                        {{ $fee_period_enum->label() }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <div class="flex justify-end w-full">
                <flux:button wire:click="extendSubscription">{{__('interface.extend')}}</flux:button>
            </div>

        </div>
        <div class="hidden lg:flex justify-center">
            <flux:separator :vertical="true"/>
        </div>
        <div class="space-y-2">
            <flux:text class="text-lg lg:text-2xl dark:text-white text-black">{{__('interface.recent_transactions')}}</flux:text>
            <div class="lg:mt-10 grid grid-cols-3 gap-4">
                <flux:text>{{__('interface.product_name')}}</flux:text>
                <flux:text>{{__('interface.amount')}}</flux:text>
                <flux:text>{{__('interface.date')}}</flux:text>
                @if(!empty($this->last_transactions))
                    @foreach($this->last_transactions as $transaction)
                        <flux:text>{{$transaction->description}}</flux:text>
                        <flux:text>@if($transaction->type === BalanceTransactionTypeEnum::PURCHASE)
                                -
                            @endif{{$transaction->amount}} €
                        </flux:text>
                        <flux:text>{{$transaction->created_at}}</flux:text>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
