<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage; 

class EventController extends Controller
{
    public function createEvent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'is_public' => 'required|boolean',
            'image' => 'nullable|string', 
            'organizer_id' => 'required|exists:organizers,id',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
    
        $validatedData = $request->all();
        if (isset($request->image)) {
            $imageData = explode(',', $request->image)[1]; 
            $image = base64_decode($imageData); 
            $imageName = 'events/' . uniqid() . '.png'; 
            Storage::disk('public')->put($imageName, $image);
            $validatedData['image'] = "https://ticketopia-backend-main-dc9cem.laravel.cloud/storage/" . $imageName;
        }
    
        $event = Event::create($validatedData);
    
        return response()->json($event, 201);
    }    

    public function getEvents()
{
    $events = Event::with(['eventDates.venue', 'ticketPrices'])->get();

    return response()->json($events->map(function ($event) {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'is_public' => $event->is_public,
            'organizer_id' => $event->organizer_id,
            'image' => $event->image,
            'dates' => $event->eventDates->map(function ($date) {
                return [
                    'id' => $date->id,
                    'start_date' => $date->start_date_time,
                    'end_date' => $date->end_date_time,
                    'venue_id' => $date->venue_id, 
                    'location' => $date->venue ? $date->venue->name : null,
                ];
            }),
            'ticket_prices' => $event->ticketPrices->map(function ($price) {
                return [
                    'id' => $price->id,
                    'event_date_id' => $price->event_date_id,
                    'ticket_type' => $price->ticket_type,
                    'price' => $price->price,
                ];
            }),
        ];
    }));
}

    public function deleteEvent($id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        $event->delete();

        return response()->json(['message' => 'Event deleted successfully'], 200);
    }

    public function updateEvent(Request $request, $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'is_public' => 'sometimes|boolean',
            'image' => 'nullable|string',
            'organizer_id' => 'sometimes|exists:organizers,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $validatedData = $request->all();

        if (isset($request->image)) {
            if (str_starts_with($request->image, 'data:image')) {
                $imageDataParts = explode(',', $request->image);
        
                if (count($imageDataParts) === 2) {
                    $imageData = base64_decode($imageDataParts[1]);
                    $imageName = 'events/' . uniqid() . '.png';
                    Storage::disk('public')->put($imageName, $imageData);
                    $validatedData['image'] = "https://ticketopia-backend-main-dc9cem.laravel.cloud/storage/" . $imageName;
                }
            } else {
                $validatedData['image'] = $request->image;
            }
        }

        $event->update($validatedData);

        return response()->json($event, 200);
    }
}
