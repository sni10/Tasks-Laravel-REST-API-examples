<?php

namespace App\Http\Controllers\Api;

use App\Contracts\TokenServiceInterface;
use App\Contracts\UserServiceInterface;
use App\Http\Controllers\Controller;
use App\Services\TokenService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected UserService $userService;
    protected TokenService $tokenService;

    public function __construct(UserServiceInterface $userService, TokenServiceInterface $tokenService)
    {
        $this->userService = $userService;
        $this->tokenService = $tokenService;
    }

    public function register(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
            ]);
            $user = $this->userService->createUser($validated);
            $accessToken = $this->tokenService->createToken($user->getAuthIdentifier());
            $refreshToken = $this->tokenService->createRefresh($user->getAuthIdentifier());
            return response()->json([
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer'
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }


    public function refresh(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'refresh_token' => 'required|string',
            ]);
            $refreshToken = $request->refresh_token;
            if(
                $this->tokenService->validateRefresh($refreshToken) and
                !$this->tokenService->validateAccessToken($refreshToken)
            ) {
                $accessToken = $this->tokenService->renewAccessToken($refreshToken);
                return response()->json([
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                    'token_type' => 'Bearer'
                ], 201);
            }

            return response()->json(['error' => 'Session refresh expire'], 403);

        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => 'required|string|email|max:255',
                'password' => 'required|string|min:8',
            ]);

            $user = $this->userService->authUser($validated);

            if (!$user) {
                return response()->json(['error' => [
                    'email' => ['The provided credentials are incorrect.'],
                ]], 401);
            }

            $accessToken = $this->tokenService->createToken($user->getAuthIdentifier());
            $refreshToken = $this->tokenService->createRefresh($user->getAuthIdentifier());

            return response()->json([
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer'
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $this->tokenService->revokeToken($request->user()->getAuthIdentifier());
            $this->tokenService->revokeRefresh($request->user()->getAuthIdentifier());
            return response()->json(['message' => 'Successfully logged out']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
        }
    }
}

