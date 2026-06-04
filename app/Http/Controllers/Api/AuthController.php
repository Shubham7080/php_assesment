<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'password' => $request->string('password'),
            'role' => $request->input('role', UserRole::Author->value),
        ]);

        $token = Auth::login($user);

        return $this->respondWithToken($token, $user, 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials);

        if (! $token) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        return $this->respondWithToken($token, Auth::user());
    }

    public function profile(): JsonResponse
    {
        return response()->json(['data' => new UserResource(Auth::user())]);
    }

    public function logout(): JsonResponse
    {
        Auth::logout();

        return response()->json(['message' => 'Successfully logged out.']);
    }

    private function respondWithToken(string $token, User $user, int $status = 200): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
            'user' => new UserResource($user),
        ], $status);
    }
}
