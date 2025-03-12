<?php

namespace App\Http\Controllers;

use App\Models\TicketPrice;
use Illuminate\Http\Request;

class TicketPriceController extends Controller
{
    public function index()
    {
        $ticketPrices = TicketPrice::all();
        return response()->json($ticketPrices);
    }

    public function show($id)
    {
        $ticketPrice = TicketPrice::findOrFail($id);
        return response()->json($ticketPrice);
    }

    public function createTicketPrice(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'ticket_type' => 'required|string',
            'price' => 'required|numeric',
            'event_date_id' => 'required|exists:event_dates,id',
        ]);

        $ticketPrice = TicketPrice::create($request->all());
        return response()->json($ticketPrice, 201);
    }

    public function updateTicketPrice(Request $request, $id)
    {
        $request->validate([
            'event_id' => 'sometimes|exists:events,id',
            'ticket_type' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'event_date_id' => 'required|exists:event_dates,id',
        ]);

        $ticketPrice = TicketPrice::findOrFail($id);
        $ticketPrice->update($request->all());
        return response()->json($ticketPrice);
    }

    public function destroy($id)
    {
        $ticketPrice = TicketPrice::findOrFail($id);
        $ticketPrice->delete();
        return response()->json(null, 204);
    }
}
