<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organizer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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
                'organizer_email' => 'required|email|max:255|unique:organizers,organizer_email',
                'organizer_phone' => 'sometimes|required|string|max:20|regex:/^\+?[0-9\s\-\(\)]+$/',
                'organizer_address' => 'required|string|min:1',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3072',
            ]);

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('organizers', 'public');
                $validatedData['image'] = Storage::url($imagePath);
                
                Log::info('Organizer image uploaded', ['path' => $imagePath, 'url' => $validatedData['image']]);
            }

            $organizer = Organizer::create($validatedData);
            return response()->json(['organizer' => $organizer], 201);
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

    public function editOrganizer(Request $request, $id)
    {
        try {
            Log::info('Edit organizer request received', [
                'organizer_id' => $id,
                'has_image' => $request->hasFile('image'),
                'all_inputs' => $request->all()
            ]);
            
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
                Log::info('Processing image for organizer update', [
                    'organizer_id' => $id,
                    'image_size' => $request->file('image')->getSize(),
                    'image_type' => $request->file('image')->getMimeType()
                ]);
                
                if ($organizer->image) {
                    $oldImagePath = str_replace('/storage/', '', parse_url($organizer->image, PHP_URL_PATH));
                    Log::info('Attempting to delete old image', ['path' => $oldImagePath]);
                    
                    if (Storage::disk('public')->exists($oldImagePath)) {
                        Storage::disk('public')->delete($oldImagePath);
                        Log::info('Old image deleted successfully');
                    } else {
                        Log::warning('Old image file not found', ['path' => $oldImagePath]);
                    }
                }
                
                try {
                    $imagePath = $request->file('image')->store('organizers', 'public');
                    Log::info('New image stored successfully', ['path' => $imagePath]);
                    $validated['image'] = Storage::url($imagePath);
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

            $organizer->update($validated);
            Log::info('Organizer updated successfully', ['organizer_id' => $organizer->id]);
            
            return response()->json(['message' => 'Organizer updated successfully!', 'organizer' => $organizer], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in organizer update', [
                'organizer_id' => $id,
                'errors' => $e->errors()
            ]);
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Organizer not found', ['organizer_id' => $id]);
            return response()->json([
                'message' => 'Organizer not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error updating organizer', [
                'organizer_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteOrganizer($id)
    {
        try {
            $organizer = Organizer::findOrFail($id);
            
            if ($organizer->image) {
                $oldImagePath = str_replace('/storage/', '', parse_url($organizer->image, PHP_URL_PATH));
                if (Storage::disk('public')->exists($oldImagePath)) {
                    Storage::disk('public')->delete($oldImagePath);
                    Log::info('Organizer image deleted successfully', ['path' => $oldImagePath]);
                }
            }
            
            $organizer->delete();
            return response()->json(['message' => 'Organizer deleted successfully!'], 200);
        } catch (\Exception $e) {
            Log::error('Delete organizer error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error deleting organizer', 'error' => $e->getMessage()], 500);
        }
    }
}