<?php

use App\Actions\CreateStripeCheckout;
use App\Enums\BalanceTransactionTypeEnum;
use App\Enums\KeyTypesEnum;
use App\Enums\MessageSenderRolesEnum;
use App\Livewire\Traits\GlobalNotifyEvent;
use App\Models\Balance;
use App\Models\Key;
use App\Models\Message;
use App\Models\Site;
use App\Models\SiteSelector;
use Carbon\Carbon;
use Livewire\Volt\Component;
use App\Enums\SystemUsageFeePeriodEnum;

new class extends Component {

    use GlobalNotifyEvent;

    const TOKEN_USE_FEE = 0.00001;

    public ?Site $site = null;
    public float $balance = 0;
    public ?Key $ownerKey = null;

    public float $upload_amount = 0;
    public bool $p_policy = false;
    public bool $terms = false;

    public int $message_count = 0;
    public int $bot_message_count = 0;
    public int $user_message_count = 0;
    public int $used_tokens_count = 0;

    public SystemUsageFeePeriodEnum $selected_period = SystemUsageFeePeriodEnum::MONTHLY;

    public function mount(SiteSelector $site_selector): void
    {
        if (!$site_selector->hasSite()) {
            redirect()->route('site.picker')->with('error', __('interface.missing_site'));
            return;
        }

        if (isset($_GET['payment_failed']) && (bool)$_GET['payment_failed']) {
            $this->notify('danger', __('interface.purchase_failed'));
        }

        $this->site = $site_selector->getSite();

        $this->balance = round($this->getBalance(), 2);

        $this->last_transactions = $this->getLastTransactions();

        $this->ownerKey = $this->site->keys()->where('keys.type', KeyTypesEnum::CUSTOMER)->first();

        $base_query = Message::query()
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->whereIn('sender_role', [
                MessageSenderRolesEnum::BOT,
                MessageSenderRolesEnum::USER,
            ]);

        $this->message_count = (clone $base_query)->count();
        $this->bot_message_count = (clone $base_query)
            ->where('sender_role', MessageSenderRolesEnum::BOT)
            ->count();
        $this->user_message_count = (clone $base_query)
            ->where('sender_role', MessageSenderRolesEnum::USER)
            ->count();

        $this->used_tokens_count = Message::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->sum('token_count');
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
            'terms' => ['accepted'],
            'p_policy' => ['accepted'],
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
                    $this->notify('success', __('interface.extend_subscription_success'));
                });
            } else {
                $this->notify('danger', __('interface_extend_subscription_failed_no_money'));
            }
        } catch (\Throwable $e) {
            $this->notify('error', __('interface.extend_subscription_failed'));
        }
    }

}; ?>
<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <x-notification.panel :notifications="session()->all()"/>
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <div
            class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-10">
            <flux:text class="text-lg lg:text-2xl dark:text-white text-black">
                @lang('interface.monthly_statistics')
            </flux:text>
            <div>
                <flux:text class="mt-4 text-base lg:text-base md:text-sm font-semibold space-y-1">
                    {{ __('interface.messages_this_month') }}: {{ $this->message_count }}
                    <br>
                    {{ __('interface.bot_messages_this_month') }}: {{ $this->bot_message_count }}
                    <br>
                    {{ __('interface.user_messages_this_month') }}: {{ $this->user_message_count }}
                    <br>
                    <br>
                    {{__('interface.used_tokens_count')}}: {{$this->used_tokens_count}}
                    <br>
                </flux:text>
            </div>
        </div>
        <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
            <flux:text class="text-lg lg:text-2xl dark:text-white text-black">
                {{__('interface.monthly_token_amount')}}
            </flux:text>
            <br>
            <div>
                <flux:text class="mt-4 text-base lg:text-xl font-semibold space-y-1">
                    {{$this->used_tokens_count*self::TOKEN_USE_FEE}} €
                </flux:text>
            </div>
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
                <flux:field variant="inline">
                    <flux:checkbox wire:model="p_policy"/>
                    <flux:label><a target="_blank" href="{{route('privacy-policy')}}">@lang('interface.accept_p_policy')</a></flux:label>
                    <flux:error name="p_policy"/>
                </flux:field>
                <flux:field variant="inline">
                    <flux:checkbox wire:model="terms"/>
                    <flux:label><a target="_blank" href="{{route('terms-and-conditions')}}">@lang('interface.accept_gtc')</a></flux:label>
                    <flux:error name="terms"/>
                </flux:field>
                <div class="w-full justify-end flex">
                    <flux:button wire:click="uploadAmount"
                                 class="space-x-2 sm:space-x-0 self-end">{{__('interface.pay')}}</flux:button>
                </div>
            </div>

            <div class="my-4">
                <flux:separator/>
            </div>
            <flux:text
                class="text-lg lg:text-2xl dark:text-white text-black">@lang('interface.system_usage')</flux:text>
            <flux:text>{{date("Y. M. d.", strtotime($this->ownerKey->expiration_time))}}</flux:text>
            <flux:select wire:model="selected_period"
                         label="{{__('interface.system_usage_packages')}}">
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
        <div class="flex lg:hidden justify-center my-4">
            <flux:separator/>
        </div>
        <div class="space-y-2">
            <flux:text
                class="text-lg lg:text-2xl dark:text-white text-black">{{__('interface.recent_transactions')}}</flux:text>
            <div class="lg:mt-10 grid grid-cols-3 gap-4">
                <flux:text>{{__('interface.product_name')}}</flux:text>
                <flux:text>{{__('interface.amount')}}</flux:text>
                <flux:text>{{__('interface.date')}}</flux:text>
                @if(!empty($this->last_transactions))
                    @foreach($this->last_transactions as $transaction)
                        <flux:text>{{$transaction->description}}</flux:text>
                        <flux:text>@if($transaction->type === BalanceTransactionTypeEnum::PURCHASE)
                                -
                            @endif{{round($transaction->amount,2)}} €
                        </flux:text>
                        <flux:text>{{$transaction->created_at}}</flux:text>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
