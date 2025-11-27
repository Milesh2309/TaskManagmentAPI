<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // AUTH CONTROLLER (Simple explanation for interviewers)
    // Purpose: authenticate a user with email/password and return a personal access token.
    // Behavior: validates input, checks credentials, and uses Laravel Sanctum to
    // create a personal access token which is returned in the response as {"token": "<token>"}.
    // The returned token should be used in requests as an Authorization Bearer token.

    public function login(Request $request)
    {
        // validate incoming request — simple rules: email and password required
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // find user by email
        $user = User::where('email', $data['email'])->first();

        // check credentials: if missing or password does not match, return 401
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // create a Sanctum personal access token (plain text). Client stores this.
        $token = $user->createToken('api-token')->plainTextToken;

        // return the token in a small JSON envelope. Use as: Authorization: Bearer <token>
        return response()->json(['token' => $token], 200);
    }
}

