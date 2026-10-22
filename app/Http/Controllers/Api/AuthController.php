<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //user signup
    public function signup(Request $request): JsonResponse{
        
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:owner,manager,cashier',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
    }
    
    //user login
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid email or password'], 401);
        }

        $token = $user->createToken('api-token')->accessToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

    // User Logout and revoke the API access token.
     
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->token()?->revoke();

        return response()->json(['message' => 'User logged out']);
    }

    //Get the authenticated user's profile.
    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
