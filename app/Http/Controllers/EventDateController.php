<?php

namespace App\Http\Controllers;

use App\Models\EventDate;
use Illuminate\Http\Request;

class EventDateController extends Controller
{
    public function index()
    {
        $eventDates = EventDate::all();
        return response()->json($eventDates);
    }

    public function show($id)
    {
        $eventDate = EventDate::findOrFail($id);
        return response()->json($eventDate);
    }

    public function createEventDate(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'start_date_time' => 'required|date',
            'end_date_time' => 'required|date|after:start_date_time',
            'venue_id' => 'required|exists:venues,id',
        ]);

        $eventDate = EventDate::create($request->all());
        return response()->json($eventDate, 201);
    }

    public function updateEventDate(Request $request, $id)
    {
        $request->validate([
            'event_id' => 'sometimes|exists:events,id',
            'start_date_time' => 'sometimes|date',
            'end_date_time' => 'sometimes|date|after:start_date_time',
            'venue_id' => 'required|exists:venues,id',
        ]);

        $eventDate = EventDate::findOrFail($id);
        $eventDate->update($request->all());
        return response()->json($eventDate);
    }

    public function destroy($id)
    {
        $eventDate = EventDate::findOrFail($id);
        $eventDate->delete();
        return response()->json(null, 204);
    }
}
