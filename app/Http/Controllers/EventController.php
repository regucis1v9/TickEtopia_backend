<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage; 
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    public function createEvent(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'is_public' => 'required|boolean',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3072', // Updated to handle file upload
                'organizer_id' => 'required|exists:organizers,id',
            ]);
        
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }
        
            $validatedData = $request->all();
            
            // Handle image upload like in OrganizerController
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('events', 'public');
                $validatedData['image'] = Storage::url($imagePath);
                
                Log::info('Event image uploaded', [
                    'path' => $imagePath, 
                    'url' => $validatedData['image']
                ]);
            }
        
            $event = Event::create($validatedData);
        
            return response()->json($event, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error', ['errors' => $e->errors()]);
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Server error', [
                'error' => $e->getMessage(), 
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Server error', 'error' => $e->getMessage()], 500);
        }
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
        try {
            $event = Event::findOrFail($id);

            // Delete image if exists
            if ($event->image) {
                $oldImagePath = str_replace('/storage/', '', parse_url($event->image, PHP_URL_PATH));
                if (Storage::disk('public')->exists($oldImagePath)) {
                    Storage::disk('public')->delete($oldImagePath);
                    Log::info('Event image deleted successfully', ['path' => $oldImagePath]);
                }
            }

            $event->delete();

            return response()->json(['message' => 'Event deleted successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Delete event error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error deleting event', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateEvent(Request $request, $id)
    {
        try {
            $event = Event::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'is_public' => 'sometimes|boolean',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3072', // Updated to handle file upload
                'organizer_id' => 'sometimes|exists:organizers,id',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $validatedData = $request->all();

            // Handle image upload like in OrganizerController
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($event->image) {
                    $oldImagePath = str_replace('/storage/', '', parse_url($event->image, PHP_URL_PATH));
                    if (Storage::disk('public')->exists($oldImagePath)) {
                        Storage::disk('public')->delete($oldImagePath);
                        Log::info('Old event image deleted successfully', ['path' => $oldImagePath]);
                    }
                }

                // Store new image
                $imagePath = $request->file('image')->store('events', 'public');
                $validatedData['image'] = Storage::url($imagePath);
                Log::info('New event image stored successfully', ['path' => $imagePath]);
            }

            $event->update($validatedData);

            return response()->json($event, 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in event update', [
                'event_id' => $id,
                'errors' => $e->errors()
            ]);
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Event not found', ['event_id' => $id]);
            return response()->json([
                'message' => 'Event not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error updating event', [
                'event_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}