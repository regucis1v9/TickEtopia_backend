<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class CheckoutController extends Controller
{
    // Hardcoded Stripe keys (make sure to rotate if exposed previously)
    private $stripeSecret;
    private $stripeWebhookSecret;
    
    public function __construct()
    {
        $this->stripeSecret = config('stripe.secret');
        $this->stripeWebhookSecret = config('stripe.webhook_secret');
    }

    public function createCheckoutSession(Request $request)
    {
        try {
            Stripe::setApiKey($this->stripeSecret);

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
                'success_url' => 'https://ticketopia-backend-main-dc9cem.laravel.cloud/checkout/success',
                'cancel_url' => 'https://ticketopia-backend-main-dc9cem.laravel.cloud/checkout/cancel',
                'metadata' => [
                    'user_id' => (string) $userId, 
                    'event_ids' => implode(',', array_column($cartItems, 'event_id')), 
                ],
            ]);
            
            \Log::info('Stripe session created:', $checkout_session->toArray());
            \Log::info('Checkout session ID', ['id' => $checkout_session->id]);


            return response()->json(['url' => $checkout_session->url]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function handleWebhook(Request $request)
    {
        Stripe::setApiKey($this->stripeSecret);

        $payload = @file_get_contents("php://input");
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        $endpoint_secret = $this->stripeWebhookSecret;

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Webhook error: ' . $e->getMessage()], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            $userId = $session->metadata->user_id ?? null;
            $eventIdsString = $session->metadata->event_ids ?? '';
            $eventIds = array_filter(explode(',', $eventIdsString));

            \Log::info('Webhook received - session metadata', [
                'metadata' => $session->metadata,
                'full_session' => $session,
            ]);

            if (!$userId || empty($eventIds)) {
                \Log::error("Missing userId or eventIds", [
                    'user_id' => $userId,
                    'event_ids_raw' => $session->metadata->event_ids ?? null,
                    'event_ids_parsed' => $eventIds,
                ]);
                return response()->json(['error' => 'Missing userId or eventIds'], 400);
            }

            // Get the "Purchased" status ID
            $purchasedStatusId = \App\Models\TicketStatus::where('name', 'Purchased')->first()?->id;
            if (!$purchasedStatusId) {
                \Log::error("'Purchased' status not found in ticket_statuses table");
                $purchasedStatusId = 1; // Fallback to ID 1
            }

            foreach ($eventIds as $eventId) {
                // Create ticket
                $ticket = \App\Models\Ticket::create([
                    'user_id' => $userId,
                    'event_id' => $eventId,
                    'ticket_number' => 'TICKET-' . strtoupper(uniqid())
                ]);
                
                // Create ticket history record
                \App\Models\TicketHistory::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $userId,
                    'status_id' => $purchasedStatusId,
                    'description' => 'Ticket purchased via Stripe payment'
                ]);
            }

            \Log::info("Tickets and history records created for user $userId for events: " . implode(', ', $eventIds));
            \Log::info('Webhook triggered for session', ['id' => $session->id]);

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
