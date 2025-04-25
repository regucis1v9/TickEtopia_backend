<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\User;
use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;

class TicketController extends Controller
{
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

        $pdfUrl = route('generate.ticket', ['ticketId' => $ticket->id]);

        return response()->json([
            'status' => 'success',
            'pdf_url' => $pdfUrl,
        ]);
    }

    public function generateTicket($ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);
        $event = Event::findOrFail($ticket->event_id);
        $user = User::findOrFail($ticket->user_id);

        $pdf = Pdf::loadView('tickets.ticket', compact('event', 'user', 'ticket'));

        return $pdf->download("ticket_{$ticket->id}.pdf");
    }
}
