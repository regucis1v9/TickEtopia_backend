<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class CheckoutController extends Controller
{
    public function createCheckoutSession(Request $request)
    {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));

            $cartItems = $request->input('cartItems', []);
            
            if (empty($cartItems)) {
                return response()->json(['error' => 'Cart is empty'], 400);
            }

            $lineItems = [];

            foreach ($cartItems as $item) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $item['event_name'], 
                            'description' => $item['event_description'] ?? 'No description available',
                            'images' => !empty($item['event_image']) ? [$item['event_image']] : [],
                        ],
                        'unit_amount' => $item['price'], // Convert to cents
                    ],
                    'quantity' => $item['quantity'],
                ];
            }

            $checkout_session = Session::create([
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => env('APP_URL') . '/checkout/success',
                'cancel_url' => env('APP_URL') . '/checkout/cancel',
            ]);

            return response()->json(['url' => $checkout_session->url]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function success()
    {
        return view('checkout.success');
    }

    public function cancel()
    {
        return view('checkout.cancel');
    }
}
