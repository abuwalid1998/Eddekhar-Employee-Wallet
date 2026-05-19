<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * @author Amjad Khaliliah
 */
class AuthController extends Controller
{
    public function __construct(
        private JwtService $jwtService
    ) {}

    public function signUp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ]);

        $user = User::create([
            'name' => explode('@', $validated['email'])[0],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $token = $this->jwtService->generateToken($user);

        return response()->json([
            'message' => 'User registered successfully.',
            'data' => [
                'user_id' => $user->id,
                'email' => $user->email,
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in_minutes' => 120,
            ],
        ], 201);
    }

    public function signIn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        $token = $this->jwtService->generateToken($user);

        return response()->json([
            'message' => 'Signed in successfully.',
            'data' => [
                'user_id' => $user->id,
                'email' => $user->email,
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in_minutes' => 120,
            ],
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        return $this->signIn($request);
    }
}
