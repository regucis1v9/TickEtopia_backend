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
            $userId = $request->input('user_id');

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
                        'unit_amount' => $item['price'],
                    ],
                    'quantity' => $item['quantity'],
                ];
            }

            \Log::info('Creating Stripe Checkout session', [
                'user_id' => $userId,
                'cartItems' => $cartItems
            ]);
            
            $checkout_session = Session::create([
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => env('APP_URL') . '/checkout/success',
                'cancel_url' => env('APP_URL') . '/checkout/cancel',
                'metadata' => [
                    'user_id' => $userId, 
                    'event_ids' => json_encode(array_column($cartItems, 'event_id')), 
                ],
            ]);
            
            \Log::info('Stripe session created:', $checkout_session->toArray());

            return response()->json(['url' => $checkout_session->url]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function handleWebhook(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $payload = @file_get_contents("php://input");
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Webhook error: ' . $e->getMessage()], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            $userId = $session->metadata->user_id ?? null;
            $eventIds = json_decode($session->metadata->event_ids ?? '[]');

            if (!$userId || empty($eventIds)) {
                \Log::error("Missing userId or eventIds", [
                    'user_id' => $userId,
                    'event_ids' => $eventIds
                ]);
                return response()->json(['error' => 'Missing userId or eventIds'], 400);
            }

            foreach ($eventIds as $eventId) {
                \App\Models\Ticket::create([
                    'user_id' => $userId,
                    'event_id' => $eventId,
                    'ticket_number' => 'TICKET-' . strtoupper(uniqid())
                ]);
            }

            \Log::info("Tickets created for user $userId for events: " . implode(', ', $eventIds));
        }

        return response()->json(['status' => 'success']);
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
