<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\TicketStatus;
use App\Models\User;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
        // Enable detailed error logging
        Log::setDefaultDriver('daily');
        Log::info('Ticket Creation Attempt', [
            'request_data' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        try {
            // Validate incoming request
            $validatedData = $request->validate([
                'user_id' => 'required|exists:users,id',
                'event_id' => 'required|exists:events,id'
            ]);

            // Start database transaction for atomicity
            return DB::transaction(function () use ($validatedData) {
                $userId = $validatedData['user_id'];
                $eventId = $validatedData['event_id'];

                // Detailed logging of validation
                Log::info('Validated Data', [
                    'user_id' => $userId,
                    'event_id' => $eventId
                ]);

                // Verify user and event exist
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

                // Log successful creation
                Log::info('Ticket and History Created Successfully', [
                    'ticket_id' => $ticket->id,
                    'history_id' => $ticketHistory->id
                ]);

                // Generate PDF URL (adjust route name as needed)
                $pdfUrl = route('generate.ticket', ['ticketId' => $ticket->id]);

                return response()->json([
                    'status' => 'success',
                    'ticket_id' => $ticket->id,
                    'pdf_url' => $pdfUrl
                ]);
            });

        } catch (\Illuminate\Validation\ValidationException $validationError) {
            // Log validation errors
            Log::error('Validation Error', [
                'errors' => $validationError->errors(),
                'input' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validationError->errors()
            ], 422);

        } catch (\Exception $e) {
            // Log any unexpected errors
            Log::error('Ticket Creation Failed', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create ticket: ' . $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
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
                        $query->select('id', 'title as name', 'description', 'image as image_url');
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