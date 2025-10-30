<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class CreateStripeCheckout
{
    use AsAction;

    /**
     * @param int $amount
     * @param string $currency
     * @param string $product_name
     * @param $metadata
     * @param $billing_address_collection
     * @return Session
     * @throws ApiErrorException
     */
    public function handle(int $amount, string $currency = 'eur', string $product_name = 'Demo product', $metadata = [], $billing_address_collection ='required'): Session
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        return Session::create([
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'billing_address_collection' => $billing_address_collection,
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
