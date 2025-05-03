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
            Log::info('Create event request received', [
                'has_image' => isset($request->image),
                'request_data' => $request->except(['image'])
            ]);
            
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'is_public' => 'required|boolean',
                'image' => 'nullable|string',
                'organizer_id' => 'required|exists:organizers,id',
            ]);
        
            if ($validator->fails()) {
                Log::error('Event validation failed', ['errors' => $validator->errors()]);
                return response()->json($validator->errors(), 422);
            }
        
            $validatedData = $request->all();
            
            if (isset($request->image) && !empty($request->image)) {
                try {
                    Log::info('Processing base64 image for event');
                    
                    // Check if it's a base64 image
                    if (strpos($request->image, 'data:image') === 0) {
                        $imageDataParts = explode(',', $request->image);
                        
                        if (count($imageDataParts) === 2) {
                            $imageData = base64_decode($imageDataParts[1]);
                            
                            if ($imageData === false) {
                                Log::error('Failed to decode base64 image data');
                                throw new \Exception('Invalid base64 image data');
                            }
                            
                            $imageName = 'events/' . uniqid() . '.png';
                            Storage::disk('public')->put($imageName, $imageData);
                            $validatedData['image'] = Storage::url($imageName);
                            
                            Log::info('Image successfully stored', [
                                'path' => $imageName,
                                'url' => $validatedData['image']
                            ]);
                        } else {
                            Log::error('Invalid base64 image format');
                            throw new \Exception('Invalid base64 image format');
                        }
                    } else {
                        // If not base64, just use the URL as is
                        $validatedData['image'] = $request->image;
                        Log::info('Using image URL as provided', ['url' => $validatedData['image']]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error processing image', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    return response()->json([
                        'message' => 'Error processing image',
                        'error' => $e->getMessage()
                    ], 500);
                }
            }
        
            $event = Event::create($validatedData);
            Log::info('Event created successfully', ['event_id' => $event->id]);
            
            return response()->json($event, 201);
        } catch (\Exception $e) {
            Log::error('Error creating event', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Server error',
                'error' => $e->getMessage()
            ], 500);
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
            Log::info('Update event request received', [
                'event_id' => $id,
                'has_image' => isset($request->image),
                'request_data' => $request->except(['image'])
            ]);
            
            $event = Event::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'is_public' => 'sometimes|boolean',
                'image' => 'nullable|string',
                'organizer_id' => 'sometimes|exists:organizers,id',
            ]);

            if ($validator->fails()) {
                Log::error('Event update validation failed', ['errors' => $validator->errors()]);
                return response()->json($validator->errors(), 422);
            }

            $validatedData = $request->all();

            if (isset($request->image) && !empty($request->image)) {
                try {
                    // Handle base64 image
                    if (strpos($request->image, 'data:image') === 0) {
                        $imageDataParts = explode(',', $request->image);
                        
                        if (count($imageDataParts) === 2) {
                            // Delete old image if exists
                            if ($event->image) {
                                $oldImagePath = str_replace('/storage/', '', parse_url($event->image, PHP_URL_PATH));
                                if (Storage::disk('public')->exists($oldImagePath)) {
                                    Storage::disk('public')->delete($oldImagePath);
                                    Log::info('Old event image deleted', ['path' => $oldImagePath]);
                                }
                            }
                            
                            $imageData = base64_decode($imageDataParts[1]);
                            
                            if ($imageData === false) {
                                Log::error('Failed to decode base64 image data');
                                throw new \Exception('Invalid base64 image data');
                            }
                            
                            $imageName = 'events/' . uniqid() . '.png';
                            Storage::disk('public')->put($imageName, $imageData);
                            $validatedData['image'] = Storage::url($imageName);
                            
                            Log::info('New event image stored', [
                                'path' => $imageName,
                                'url' => $validatedData['image']
                            ]);
                        } else {
                            Log::error('Invalid base64 image format');
                            throw new \Exception('Invalid base64 image format');
                        }
                    } else {
                        // If not base64, just use the URL as is
                        $validatedData['image'] = $request->image;
                        Log::info('Using image URL as provided', ['url' => $validatedData['image']]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error processing image for update', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    return response()->json([
                        'message' => 'Error processing image',
                        'error' => $e->getMessage()
                    ], 500);
                }
            }

            $event->update($validatedData);
            Log::info('Event updated successfully', ['event_id' => $event->id]);
            
            return response()->json($event, 200);
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