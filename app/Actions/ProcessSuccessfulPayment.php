<?php

namespace App\Actions;

use App\Enums\BalanceTransactionTypeEnum;
use App\Enums\KeyTypesEnum;
use App\Mail\TokenMail;
use App\Models\Balance;
use App\Models\Key;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class ProcessSuccessfulPayment
{
    use AsAction;

    public function handle(string $session_id)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        if (!$session_id) {
            return $this->failed();
        }

        try {
            $session = Session::retrieve($session_id);

            if ($session->payment_status !== 'paid') {
                return $this->failed();
            }

            $user_id     = $session->metadata->user_id ?? null;
            $key_id      = $session->metadata->key_id ?? null;
            $days_number = $session->metadata->days_number ?? null;
            $amount      = $session->amount_total / 100;
            $note        = $session->metadata->note ?? null;
            $fee_period  = $session->metadata->fee_period ?? null;

            return DB::transaction(function () use (
                $user_id,
                $key_id,
                $days_number,
                $amount,
                $note,
                $fee_period
            ) {
                if ($this->isBalanceTopUp($user_id, $key_id, $amount)) {
                    return $this->processBalanceTopUp($user_id, $key_id, $amount, $note);
                }

                if ($this->isLicensePurchase($user_id, $amount, $days_number, $fee_period)) {
                    return $this->processLicensePurchase($user_id, $days_number, $amount, $fee_period, $note);
                }

                return $this->failed();
            });

        } catch (\Exception $e) {
            \Log::error('Sikertelen fizetés feldolgozás', [
                'session_id' => $session_id,
                'error' => $e->getMessage(),
            ]);
            return $this->failed();
        }
    }

    private function isBalanceTopUp($user_id, $key_id, $amount): bool
    {
        return $user_id && $key_id && $amount;
    }

    private function isLicensePurchase($user_id, $amount, $days_number, $fee_period): bool
    {
        return $user_id && $amount && $days_number && $fee_period;
    }

    private function processBalanceTopUp($user_id, $key_id, $amount, $note)
    {
        Balance::create([
            'user_id' => $user_id,
            'key_id' => $key_id,
            'amount' => $amount,
            'type' => BalanceTransactionTypeEnum::DEPOSIT,
            'description' => $note,
        ]);

        return to_route('dashboard')->with('success', __('interface.purchase_success'));
    }

    private function processLicensePurchase($user_id, $days_number, $amount, $fee_period, $note)
    {
        $token = $this->generateUniqueToken();

        $key = Key::create([
            'token' => $token, // biztonságos tárolás
            'type' => KeyTypesEnum::CUSTOMER,
            'expiration_time' => now()->addDays((int) $days_number),
        ]);

        $key->balances()->create([
            'user_id' => $user_id,
            'key_id' => $key->id,
            'amount' => $amount,
            'type' => BalanceTransactionTypeEnum::DEPOSIT,
            'description' => __('interface.top_up_amount'),
        ]);

        $key->balances()->create([
            'user_id' => $user_id,
            'key_id' => $key->id,
            'amount' => $fee_period,
            'type' => BalanceTransactionTypeEnum::PURCHASE,
            'description' => $note,
        ]);

        DB::afterCommit(function () use ($user_id, $token) {
            $this->sendLicenseMail($user_id, $token);
        });

        return to_route('site.picker')->with('success', __('interface.purchase_success_message'));
    }

    private function generateUniqueToken(): string
    {
        do {
            $token = Str::uuid()->toString();
            $hashed = hash('sha256', $token);
            $exists = Key::where('token', $hashed)->exists();
        } while ($exists);

        return $token;
    }

    private function sendLicenseMail(int $user_id, string $token): void
    {
        $user = User::find($user_id);

        if ($user && $user->email) {
            try {
                Mail::to($user->email)->send(new TokenMail($token, $user->name));
                \Log::info('Licenc kulcs email sikeresen elküldve', [
                    'user_id' => $user_id,
                    'email' => $user->email,
                ]);
            } catch (\Exception $e) {
                \Log::error('Hiba a licenc kulcs email küldése során', [
                    'user_id' => $user_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function failed(): RedirectResponse
    {
        return to_route('site.picker')->with('error', __('interface.purchase_failed'));
    }
}
