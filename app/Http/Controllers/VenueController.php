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

    public function addVenue(Request $request)
{
    try {
        Log::info('Starting addVenue...');

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'sometimes|required|string|max:20|regex:/^\+?[0-9\s\-\(\)]+$/',
            'capacity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3072',
        ]);
        Log::info('Validation passed');

        if ($request->hasFile('image')) {
            Log::info('Image file detected');
            
            $file = $request->file('image');
            Log::info('Original name: ' . $file->getClientOriginalName());
            Log::info('Size: ' . $file->getSize());
            Log::info('Mime type: ' . $file->getMimeType());

            $imagePath = $file->store('venues', 'public');
            Log::info('Stored image at: ' . $imagePath);

            $validatedData['image'] = config('app.url') . '/storage/' . $imagePath;
            Log::info('Image URL set: ' . $validatedData['image']);
        }

        $venue = Venue::create($validatedData);
        Log::info('Venue created: ' . $venue->id);

        return response()->json(['venue' => $venue], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Validation error', ['errors' => $e->errors()]);
        return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);

    } catch (\Illuminate\Database\QueryException $e) {
        Log::error('Database error', ['error' => $e->getMessage()]);
        return response()->json(['message' => 'Database error', 'error' => $e->getMessage()], 500);

    } catch (\Exception $e) {
        Log::error('General server error', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'message' => 'Server error',
            'error' => $e->getMessage(),
            'trace' => $e->getTrace()
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