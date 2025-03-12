<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organizer;
use Illuminate\Support\Facades\Storage; 

class OrganizerController extends Controller
{
    public function getAllOrganizers()
    {
        $organizers = Organizer::all();
        return response()->json($organizers, 200);
    }

    public function addOrganizer(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'organizer_name' => 'required|string|max:255',
                'organizer_registration_number' => 'required|string|max:500',
                'organizer_email' => 'required|email|max:255|unique:organizers',
                'organizer_phone' => 'required|string|max:20',
                'organizer_address' => 'required|string|min:1',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3072',
            ]);

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('organizers', 'public'); 
                $validatedData['image'] = "http://127.0.0.1:8000/storage/" . $imagePath; 
            }

            $organizer = Organizer::create($validatedData);
            return response()->json(['organizer' => $organizer], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['message' => 'Database error', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server error', 'error' => $e->getMessage()], 500);
        }
    }

    public function editOrganizer(Request $request, $id)
    {
        $organizer = Organizer::findOrFail($id);
        
        $validated = $request->validate([
            'organizer_name' => 'sometimes|required|string|max:255',
            'organizer_registration_number' => 'sometimes|required|string|max:500',
            'organizer_email' => 'sometimes|required|email|max:255|unique:organizers,organizer_email,' . $id,
            'organizer_phone' => 'sometimes|required|string|max:20',
            'organizer_address' => 'sometimes|required|string|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3072',
        ]);

        if ($request->hasFile('image')) {
            if ($organizer->image) {
                $oldImagePath = str_replace('http://127.0.0.1:8000/storage/', '', $organizer->image);
                Storage::disk('public')->delete($oldImagePath); 
            }
            $imagePath = $request->file('image')->store('organizers', 'public');
            $validated['image'] = "http://127.0.0.1:8000/storage/" . $imagePath; 
        }

        $organizer->update($validated);
        return response()->json(['message' => 'Organizer updated successfully!', 'organizer' => $organizer], 200);
    }

    public function deleteOrganizer($id)
    {
        $organizer = Organizer::findOrFail($id);
        if ($organizer->image) {
            $oldImagePath = str_replace('http://127.0.0.1:8000/storage/', '', $organizer->image);
            Storage::disk('public')->delete($oldImagePath);
        }
        
        $organizer->delete();
        return response()->json(['message' => 'Organizer deleted successfully!'], 200);
    }
}
