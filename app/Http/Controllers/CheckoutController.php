<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Illuminate\Support\Facades\Log;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\TicketStatus;
use App\Models\User;
use App\Models\Event;
use Exception;

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
        try {
            Stripe::setApiKey($this->stripeSecret);

            $payload = @file_get_contents("php://input");
            $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
            $endpoint_secret = $this->stripeWebhookSecret;

            try {
                $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
            } catch (Exception $e) {
                Log::error('Webhook verification failed: ' . $e->getMessage());
                return response()->json(['error' => 'Webhook verification failed'], 400);
            }

            if ($event->type === 'checkout.session.completed') {
                $session = $event->data->object;

                // Detailed logging of session data
                Log::info('Stripe Checkout Session Received', [
                    'session_id' => $session->id,
                    'metadata' => $session->metadata ?? 'No metadata',
                    'customer_email' => $session->customer_details->email ?? 'No email',
                ]);

                // Extract user and event information
                $userEmail = $session->customer_details->email ?? null;
                $userId = null;
                $eventIds = [];

                // Try to find user by email
                if ($userEmail) {
                    $user = User::where('email', $userEmail)->first();
                    $userId = $user ? $user->id : null;
                }

                // Fallback to metadata user_id
                if (!$userId && isset($session->metadata->user_id)) {
                    $userId = $session->metadata->user_id;
                }

                // Extract event IDs
                if (isset($session->metadata->event_ids)) {
                    $eventIds = array_filter(explode(',', $session->metadata->event_ids));
                }

                // Validate user and events
                if (!$userId) {
                    Log::error('No user found for webhook', [
                        'email' => $userEmail,
                        'session_metadata' => $session->metadata,
                    ]);
                    return response()->json(['error' => 'User not found'], 400);
                }

                if (empty($eventIds)) {
                    Log::error('No event IDs found in webhook', [
                        'session_metadata' => $session->metadata,
                    ]);
                    return response()->json(['error' => 'No events to process'], 400);
                }

                // Ensure user exists
                $user = User::findOrFail($userId);

                // Get or create 'Purchased' status
                $purchasedStatus = TicketStatus::firstOrCreate(
                    ['name' => 'Purchased'],
                    ['description' => 'Ticket has been purchased']
                );

                // Process each event
                $createdTickets = [];
                foreach ($eventIds as $eventId) {
                    try {
                        // Ensure event exists
                        $event = Event::findOrFail($eventId);

                        // Create ticket
                        $ticket = Ticket::create([
                            'user_id' => $userId,
                            'event_id' => $eventId,
                            'ticket_number' => 'TICKET-' . strtoupper(uniqid())
                        ]);

                        // Create ticket history
                        $ticketHistory = TicketHistory::create([
                            'ticket_id' => $ticket->id,
                            'user_id' => $userId,
                            'status_id' => $purchasedStatus->id,
                            'description' => 'Ticket purchased via Stripe payment'
                        ]);

                        $createdTickets[] = $ticket->id;

                        Log::info('Ticket and History Created', [
                            'ticket_id' => $ticket->id,
                            'user_id' => $userId,
                            'event_id' => $eventId,
                            'history_id' => $ticketHistory->id
                        ]);
                    } catch (Exception $eventError) {
                        Log::error('Failed to process event ticket', [
                            'event_id' => $eventId,
                            'user_id' => $userId,
                            'error' => $eventError->getMessage(),
                            'trace' => $eventError->getTraceAsString()
                        ]);
                    }
                }

                // Final validation
                if (empty($createdTickets)) {
                    Log::error('No tickets were created', [
                        'user_id' => $userId,
                        'event_ids' => $eventIds,
                    ]);
                    return response()->json(['error' => 'Failed to create tickets'], 500);
                }

                return response()->json([
                    'status' => 'success', 
                    'created_tickets' => $createdTickets
                ]);
            }

            return response()->json(['status' => 'ignored']);
        } catch (Exception $mainError) {
            Log::error('Catastrophic webhook error', [
                'error' => $mainError->getMessage(),
                'trace' => $mainError->getTraceAsString()
            ]);
            return response()->json(['error' => 'Unexpected error'], 500);
        }
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
