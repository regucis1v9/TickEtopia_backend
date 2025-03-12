<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name' => 'required|string|min:3|max:14',
            'email' => 'required|string|email|min:5|max:30|unique:users',
            'password' => 'required|string|min:8|max:25',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => 422,
                'message' => "Registration was not successful",
                'errors' => $validation->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $user->remember_token = $token;
        $user->save();

        return response()->json([
            'message' => 'Registration successful',
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|string|email|min:5|max:255|exists:users',
        'password' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    $user->remember_token = $token;
    $user->save();

    return response()->json([
        'message' => 'Login successful',
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ],
    ]);
}

    public function user(Request $request)
    {
        try {
            $user = $request->user(); 
    
            if (!$user) {
                \Log::error('No user found for the request.', ['request' => $request]);
                return response()->json(['message' => 'User not found'], 404);
            }
    
            return response()->json($user); 
        } catch (\Exception $e) {
            \Log::error('Error fetching user data:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'An error occurred while fetching user data'], 500);
        }
    }

    public function logout(Request $request)
    {
        $user = $request->user();
    
        if ($user) {
            \Log::info('User logging out:', ['user_id' => $user->id]);
    
            $user->tokens()->delete(); 
            
            $user->remember_token = null; 
    
            if ($user->save()) {
                \Log::info('User logged out successfully and remember_token set to null.', ['user_id' => $user->id]);
            } else {
                \Log::error('Failed to save user after logout.', ['user_id' => $user->id]);
            }
        } else {
            \Log::warning('Logout attempt without authenticated user.');
        }
    
        return response()->json(['message' => 'Logged out successfully']);
    }
}
