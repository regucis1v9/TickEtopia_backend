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
        // Validate request
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'is_public' => 'required|boolean',
            'image' => 'nullable|string', // Accept base64 strings
            'organizer_id' => 'required|exists:organizers,id',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
    
        // Handle image upload
        $validatedData = $request->all();
        if (isset($request->image)) {
            // Decode base64 string and store as image
            $imageData = explode(',', $request->image)[1]; // Get base64 string after the comma
            $image = base64_decode($imageData); // Decode base64
            $imageName = 'events/' . uniqid() . '.png'; // Generate a unique name for the image
            Storage::disk('public')->put($imageName, $image); // Store the image
            $validatedData['image'] = "https://www.ticketopia.store/storage/" . $imageName; // Set image URL
        }
    
        // Create the event
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
                'image' => $event->image,
                'dates' => $event->eventDates->map(function ($date) {
                    return [
                        'start_date' => $date->start_date_time,
                        'end_date' => $date->end_date_time,
                        'location' => $date->venue ? $date->venue->name : null,
                    ];
                }),
                'ticket_prices' => $event->ticketPrices->map(function ($price) {
                    return [
                        'ticket_type' => $price->ticket_type,
                        'price' => $price->price,
                    ];
                }),
            ];
        }));
    }
}
