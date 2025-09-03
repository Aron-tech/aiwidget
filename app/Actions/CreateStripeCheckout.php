<?php

namespace App\Actions;

use App\Models\SiteSelector;
use Lorisleiva\Actions\Concerns\AsAction;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class CreateStripeCheckout
{
    use AsAction;

    public function handle(int $amount, string $currency = 'eur', string $product_name = 'Demo product', $metadata = ['user_id' => auth()->id()], $note = 'Demo note'): Session
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        return Session::create([
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'line_items' => [[
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [
                        'name' => $product_name,
                    ],
                    'unit_amount' => $amount,
                ],
                'quantity' => 1,
            ]],
            'success_url' => url('/payment/success?session_id={CHECKOUT_SESSION_ID}'),
            'cancel_url' => url('/payment/cancel'),
            'metadata' => $metadata,
        ]);
    }
}
