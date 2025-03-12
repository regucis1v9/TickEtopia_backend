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
        // Assume that after a successful payment, we have the event and user information
        $userId = $request->user_id;
        $eventId = $request->event_id;

        // Fetch the event and user data
        $event = Event::findOrFail($eventId);
        $user = User::findOrFail($userId);

        // Create the ticket with a unique ticket number
        $ticketNumber = 'TICKET-' . strtoupper(uniqid());

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'ticket_number' => $ticketNumber
        ]);

        // Generate the PDF ticket and return the URL
        $pdfUrl = route('generate.ticket', ['ticketId' => $ticket->id]);

        return response()->json([
            'status' => 'success',
            'pdf_url' => $pdfUrl,
        ]);
    }

    public function generateTicket($ticketId)
    {
        // Fetch ticket details from the database
        $ticket = Ticket::findOrFail($ticketId);
        $event = Event::findOrFail($ticket->event_id);
        $user = User::findOrFail($ticket->user_id);

        // Pass data to Blade template and generate the PDF
        $pdf = Pdf::loadView('tickets.ticket', compact('event', 'user', 'ticket'));

        // Return the generated PDF to the user for download
        return $pdf->download("ticket_{$ticket->id}.pdf");
    }
}
