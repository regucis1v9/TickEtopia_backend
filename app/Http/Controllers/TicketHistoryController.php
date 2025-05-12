<?php

namespace App\Http\Controllers;

use App\Models\TicketHistory;
use App\Models\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Event;
use Illuminate\Http\Request;

class TicketHistoryController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'user_id' => 'required|exists:users,id',
            'status_id' => 'required|exists:ticket_statuses,id',
            'description' => 'nullable|string',
        ]);

        $history = TicketHistory::create($validated);

        return response()->json($history, 201);
    }

    public function createTicketAfterPayment(Request $request)
    {
        try {
            $userId = $request->input('user_id');
            $eventId = $request->input('event_id');

            // Validate user and event
            $user = User::findOrFail($userId);
            $event = Event::findOrFail($eventId);

            // Generate unique ticket number
            $ticketNumber = 'TICKET-' . strtoupper(uniqid());

            // Create ticket
            $ticket = Ticket::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'ticket_number' => $ticketNumber
            ]);

            // Get or create 'Purchased' status
            $purchasedStatus = TicketStatus::firstOrCreate(
                ['name' => 'Purchased'],
                ['description' => 'Ticket has been purchased']
            );

            // Create ticket history
            $ticketHistory = TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'status_id' => $purchasedStatus->id,
                'description' => 'Ticket purchased via Stripe payment'
            ]);

            // Generate PDF URL (assuming you have a route for this)
            $pdfUrl = route('generate.ticket', ['ticketId' => $ticket->id]);

            return response()->json([
                'status' => 'success',
                'ticket_id' => $ticket->id,
                'pdf_url' => $pdfUrl
            ]);

        } catch (\Exception $e) {
            Log::error('Ticket creation failed', [
                'user_id' => $userId ?? 'N/A',
                'event_id' => $eventId ?? 'N/A',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        return TicketHistory::with(['ticket', 'user', 'status'])->get();
    }
    
    // New method to get purchase history for a specific user
    public function getUserHistory(Request $request)
    {
        try {
            $userId = $request->user()->id;
            
            $history = TicketHistory::with([
                    'ticket', 
                    'status',
                    'ticket.event' => function($query) {
                        $query->select('id', 'name', 'description', 'image_url');
                    }
                ])
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'status' => 'success',
                'data' => $history
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching user history: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch history: ' . $e->getMessage()
            ], 500);
        }
    }
}