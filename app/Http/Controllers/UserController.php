<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Firebase\JWT\JWT;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages())->setStatusCode(422);
        }

        $validated = $validator->validated();

        if (Auth::attempt($validated)) {
            $user = Auth::user();

            $payload = [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'iat' => Carbon::now()->timestamp,
                'exp' => Carbon::now()->timestamp + 3600*2,
            ];

            $bearer = JWT::encode($payload, env('JWT_SECRET_KEY'), 'HS256');

            return response()->json([
                'id' => $user->id,
                'nama' => $user->name,
                'role' => $user->role,
                'token' => 'Bearer '.$bearer
            ], 200);
        }

        return response()->json(["Account Not Found"], 422);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages())->setStatusCode(422);
        }

        $validated = $validator->validate();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user' // Set a default role, adjust as necessary
        ]);

        return response()->json([
            'message' => 'Success',
            'user' => $user
        ], 201);
    }
}
