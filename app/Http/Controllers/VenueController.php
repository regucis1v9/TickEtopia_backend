<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class VenueController extends Controller
{
    public function getAllVenues()
    {
        $venues = Venue::all();
        return response()->json($venues, 200);
    }

    public function addVenue(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'address' => 'required|string|max:500',
                'contact_email' => 'required|email|max:255',
                'contact_phone' => 'sometimes|required|string|max:20|regex:/^\+?[0-9\s\-\(\)]+$/',
                'capacity' => 'required|integer|min:1',
                'notes' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3072',
            ]);

            if ($request->hasFile('image')) {
                // Store image and get path
                $imagePath = $request->file('image')->store('venues', 'public');
                // Use Storage URL helper instead of hardcoding domain
                $validatedData['image'] = config('app.url') . '/storage/' . $imagePath;
                
                // Log upload attempt
                Log::info('Image uploaded', ['path' => $imagePath, 'url' => $validatedData['image']]);
            }

            $venue = Venue::create($validatedData);
            return response()->json(['venue' => $venue], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error', ['errors' => $e->errors()]);
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Database error', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            Log::error('Server error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Server error', 'error' => $e->getMessage()], 500);
        }
    }

    public function editVenue(Request $request, $id)
{
    try {
        // Log the incoming request
        Log::info('Edit venue request received', [
            'venue_id' => $id,
            'has_image' => $request->hasFile('image'),
            'all_inputs' => $request->all()
        ]);
        
        $venue = Venue::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string|max:500',
            'contact_email' => 'sometimes|required|email|max:255',
            'contact_phone' => 'sometimes|required|string|max:20',
            'capacity' => 'sometimes|required|integer|min:1',
            'notes' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3072',
        ]);

        if ($request->hasFile('image')) {
            // Log image processing attempt
            Log::info('Processing image for venue update', [
                'venue_id' => $id,
                'image_size' => $request->file('image')->getSize(),
                'image_type' => $request->file('image')->getMimeType()
            ]);
            
            // Delete old image if exists
            if ($venue->image) {
                $oldImagePath = str_replace(config('app.url') . '/storage/', '', $venue->image);
                Log::info('Attempting to delete old image', ['path' => $oldImagePath]);
                
                if (Storage::disk('public')->exists($oldImagePath)) {
                    Storage::disk('public')->delete($oldImagePath);
                    Log::info('Old image deleted successfully');
                } else {
                    Log::warning('Old image file not found', ['path' => $oldImagePath]);
                }
            }
            
            // Upload new image
            try {
                $imagePath = $request->file('image')->store('venues', 'public');
                Log::info('New image stored successfully', ['path' => $imagePath]);
                $validated['image'] = config('app.url') . '/storage/' . $imagePath;
            } catch (\Exception $e) {
                Log::error('Failed to store image', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'message' => 'Failed to upload image',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        // Update venue with validated data
        $venue->update($validated);
        Log::info('Venue updated successfully', ['venue_id' => $venue->id]);
        
        return response()->json(['message' => 'Venue updated successfully!', 'venue' => $venue], 200);
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Validation error in venue update', [
            'venue_id' => $id,
            'errors' => $e->errors()
        ]);
        return response()->json([
            'message' => 'Validation error',
            'errors' => $e->errors()
        ], 422);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        Log::error('Venue not found', ['venue_id' => $id]);
        return response()->json([
            'message' => 'Venue not found',
            'error' => $e->getMessage()
        ], 404);
    } catch (\Exception $e) {
        Log::error('Error updating venue', [
            'venue_id' => $id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'message' => 'Server error',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function deleteVenue($id)
    {
        try {
            $venue = Venue::findOrFail($id);
            
            if ($venue->image) {
                $oldImagePath = str_replace(config('app.url') . '/storage/', '', $venue->image);
                if (Storage::disk('public')->exists($oldImagePath)) {
                    Storage::disk('public')->delete($oldImagePath);
                }
            }
            
            $venue->delete();
            return response()->json(['message' => 'Venue deleted successfully!'], 200);
        } catch (\Exception $e) {
            Log::error('Delete venue error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error deleting venue', 'error' => $e->getMessage()], 500);
        }
    }
}