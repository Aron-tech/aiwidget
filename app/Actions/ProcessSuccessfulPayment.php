<?php

namespace App\Actions;

use App\Enums\BalanceTransactionTypeEnum;
use App\Enums\KeyTypesEnum;
use App\Models\Balance;
use App\Models\Key;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class ProcessSuccessfulPayment
{
    use AsAction;

    /**
     * @throws ApiErrorException
     */
    public function handle(string $session_id)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        if (!$session_id) {
            return to_route('site.picker');
        }

        $session = Session::retrieve($session_id);

        if ($session->payment_status === 'paid') {
            $user_id = $session->metadata->user_id ?? null;
            $key_id = $session->metadata->key_id ?? null;
            $days_number = $session->metadata->days_number ?? null;
            $amount = $session->amount_total / 100;
            $note = $session->metadata->note ?? null;
            if ($user_id && $key_id && $amount) {
                Balance::create([
                    'user_id' => $user_id,
                    'key_id' => $key_id,
                    'amount' => $amount,
                    'type' => BalanceTransactionTypeEnum::CREDIT,
                    'description' => $note,
                ]);
                return to_route('dashboard');
            } else if ($user_id && $amount && $days_number) {
                $token = '';
                do {
                    $token = Str::random(40);
                    $hashed = hash('sha256', $token);
                    $exists = Key::where('token', $hashed)->exists();
                } while ($exists);

                $key = Key::create([
                    'token' => $hashed,
                    'type' => KeyTypesEnum::OWNER,
                    'expiration_time' => now()->addDays((int)$days_number),
                ]);

                $key->balances()->create([
                    'user_id' => $user_id,
                    'key_id' => $key_id,
                    'amount' => $amount,
                    'type' => BalanceTransactionTypeEnum::DEBIT,
                    'description' => $note,
                ]);
                return to_route('site.picker');
            }
        }
    }
}
