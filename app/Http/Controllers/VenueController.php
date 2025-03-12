<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venue;
use Illuminate\Support\Facades\Storage;

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
                'contact_phone' => 'sometimes|required|string|max:20|regex:/^\+?\d{1,15}$/',
                'capacity' => 'required|integer|min:1',
                'notes' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3072',
            ]);

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('venues', 'public');
                $validatedData['image'] = "http://127.0.0.1:8000/storage/" . $imagePath;
            }

            $venue = Venue::create($validatedData);
            return response()->json(['venue' => $venue], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['message' => 'Database error', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server error', 'error' => $e->getMessage()], 500);
        }
    }

    public function editVenue(Request $request, $id)
    {
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
            if ($venue->image) {
                $oldImagePath = str_replace('http://127.0.0.1:8000/storage/', '', $venue->image);
                Storage::disk('public')->delete($oldImagePath); 
            }
            $imagePath = $request->file('image')->store('venues', 'public');
            $validated['image'] = "http://127.0.0.1:8000/storage/" . $imagePath; 
        }

        $venue->update($validated);
        return response()->json(['message' => 'Venue updated successfully!', 'venue' => $venue], 200);
    }

    public function deleteVenue($id)
    {
        $venue = Venue::findOrFail($id);
        if ($venue->image) {
            $oldImagePath = str_replace('http://127.0.0.1:8000/storage/', '', $venue->image);
            Storage::disk('public')->delete($oldImagePath);
        }
        
        $venue->delete();
        return response()->json(['message' => 'Venue deleted successfully!'], 200);
    }
}
