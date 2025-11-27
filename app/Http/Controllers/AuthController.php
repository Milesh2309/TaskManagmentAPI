<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    
    
    public function login(Request $request)
    {
        
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

