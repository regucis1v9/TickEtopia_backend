<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Illuminate\Support\Facades\Log;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\TicketStatus;

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

        // Build proper indexed array
        $lineItems = array_map(function ($item) {
            return [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $item['event_name'],
                        'description' => $item['event_description'] ?? 'No description available',
                        'images' => !empty($item['event_image']) ? [$item['event_image']] : [],
                    ],
                    'unit_amount' => (int) $item['price'],
                ],
                'quantity' => (int) $item['quantity'],
            ];
        }, array_values($cartItems)); // <-- force numeric keys

        \Log::info('Creating Stripe Checkout session', [
            'user_id' => $userId,
            'cartItems' => $cartItems,
            'lineItems' => $lineItems
        ]);

        $checkout_session = Session::create([
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => 'https://ticketopia-frontend.vercel.app/history?session_id={CHECKOUT_SESSION_ID}',  // Updated URL
            'cancel_url' => 'https://ticketopia-frontend.vercel.app/cancel',
            'metadata' => [
                'user_id' => (string) $userId,
                'event_ids' => implode(',', array_column($cartItems, 'event_id')),
            ],
        ]);

        \Log::info('Stripe session created:', $checkout_session->toArray());

        return response()->json(['url' => $checkout_session->url]);

    } catch (\Exception $e) {
        \Log::error('Stripe Checkout creation failed', ['message' => $e->getMessage()]);
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
            Log::error('Webhook error: ' . $e->getMessage());
            return response()->json(['error' => 'Webhook error: ' . $e->getMessage()], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            // More robust metadata extraction
            $userId = null;
            $eventIds = [];

            // Safely extract user_id
            if (isset($session->metadata->user_id)) {
                $userId = $session->metadata->user_id;
            } elseif (isset($session->customer_details->email)) {
                // Fallback: try to find user by email
                $user = \App\Models\User::where('email', $session->customer_details->email)->first();
                $userId = $user ? $user->id : null;
            }

            // Safely extract event_ids
            if (isset($session->metadata->event_ids)) {
                $eventIds = array_filter(explode(',', $session->metadata->event_ids));
            }

            // Extensive logging for debugging
            Log::info('Webhook Metadata Processing', [
                'raw_metadata' => $session->metadata ?? 'No metadata',
                'extracted_user_id' => $userId,
                'extracted_event_ids' => $eventIds,
                'customer_email' => $session->customer_details->email ?? 'No email',
            ]);

            // Validate extracted data
            if (!$userId) {
                Log::error("Unable to determine user ID", [
                    'session_data' => $session,
                ]);
                return response()->json(['error' => 'Cannot determine user'], 400);
            }

            if (empty($eventIds)) {
                Log::error("No event IDs found", [
                    'session_data' => $session,
                ]);
                return response()->json(['error' => 'No events to process'], 400);
            }

            // Get the "Purchased" status ID
            $purchasedStatusId = TicketStatus::where('name', 'Purchased')->first()?->id ?? 1;

            // Process each event
            foreach ($eventIds as $eventId) {
                try {
                    // Create ticket
                    $ticket = Ticket::create([
                        'user_id' => $userId,
                        'event_id' => $eventId,
                        'ticket_number' => 'TICKET-' . strtoupper(uniqid())
                    ]);
                    
                    // Create ticket history record
                    TicketHistory::create([
                        'ticket_id' => $ticket->id,
                        'user_id' => $userId,
                        'status_id' => $purchasedStatusId,
                        'description' => 'Ticket purchased via Stripe payment'
                    ]);

                    Log::info("Ticket created", [
                        'ticket_id' => $ticket->id,
                        'user_id' => $userId,
                        'event_id' => $eventId,
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to create ticket for event", [
                        'event_id' => $eventId,
                        'user_id' => $userId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'ignored']);
    }

    public function getSessionDetails($sessionId)
    {
        Stripe::setApiKey($this->stripeSecret);

        try {
            $session = \Stripe\Checkout\Session::retrieve($sessionId);
            return response()->json($session);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve session data: ' . $e->getMessage()], 400);
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
