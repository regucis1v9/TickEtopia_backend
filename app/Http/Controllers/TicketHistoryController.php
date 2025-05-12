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
        $userId = $request->user_id;
        $eventId = $request->event_id;

        $event = Event::findOrFail($eventId);
        $user = User::findOrFail($userId);

        $ticketNumber = 'TICKET-' . strtoupper(uniqid());

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'ticket_number' => $ticketNumber
        ]);

        $statusId = TicketStatus::where('name', 'Purchased')->first()?->id ?? 1;

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'status_id' => $statusId,
            'description' => 'Ticket automatically added after Stripe payment'
        ]);

        $pdfUrl = route('generate.ticket', ['ticketId' => $ticket->id]);

        return response()->json([
            'status' => 'success',
            'pdf_url' => $pdfUrl,
            'ticket_id' => $ticket->id,
        ]);
    }

    public function index()
    {
        return TicketHistory::with(['ticket', 'user', 'status'])->get();
    }
    
    // New method to get purchase history for a specific user
    public function getUserHistory(Request $request)
    {
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
    }
}