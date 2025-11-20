<?php

namespace App\Repositories;

use App\Contracts\TokenRepositoryInterface;
use App\Contracts\UserServiceInterface;
use App\Models\RefreshToken;
use Illuminate\Support\Str;

class EloquentTokenRepository implements TokenRepositoryInterface
{

    protected UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    public function createRefresh($userId): string
    {
        $user = $this->userService->getUserById($userId);
        $user->refreshTokens()->delete();
        $plainTextToken = Str::random(64);
        $hashedToken = hash('sha256', $plainTextToken);
        $user->refreshTokens()->create([
            'token' => $plainTextToken,
            'expires_at' => now()->addMinutes(config('sanctum.expiration_refresh'))
        ]);
        $newToken = $user->refreshTokens()->where('expires_at', '>', now())->first();;

        return $newToken->id . '|' . $hashedToken;
    }


    public function getRefreshByHash($refreshToken): ?RefreshToken
    {
        list($tokenId, $tokenHash) = explode('|', $refreshToken);
        $refreshTokenModel = $this->getRefreshById($tokenId);
        if (hash_equals($tokenHash, hash('sha256', $refreshTokenModel->token))) {
            return $refreshTokenModel;
        }
        return null;
    }

    public function getRefreshById($tokenId): ?RefreshToken
    {
        return RefreshToken::where(['id' => $tokenId])->firstOrFail();
    }

    public function revokeRefreshToken($userId): void
    {
        $user = $this->userService->getUserById($userId);
        $user?->refreshTokens()->delete();
    }

    public function isRefreshTokenExpired($refreshToken): bool
    {
        $refreshTokenModel = $this->getRefreshByHash($refreshToken);
        if ($refreshTokenModel) {
            return $refreshTokenModel->expires_at < now();
        }
        return false;
    }

    public function validateRefreshToken($refreshToken): bool
    {
        list($tokenId, $tokenHash) = explode('|', $refreshToken);
        $refreshTokenModel = $this->getRefreshById($tokenId);
        if ($this->isRefreshTokenExpired($refreshToken)) {
            return false;
        }

        return hash_equals($tokenHash, hash('sha256', $refreshTokenModel->token));
    }

    public function validateAccessToken($refreshToken): bool
    {
        $refreshTokenModel = $this->getRefreshByHash($refreshToken);
        $accessToken = $refreshTokenModel->user->tokens[0];
        if (!$accessToken) {
            return false;
        }
        if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function createAccess($userId): string
    {
        $user = $this->userService->getUserById($userId);
        $this->revokeAccess($user->getAuthIdentifier());
        $expiration = now()->addMinutes(config('sanctum.expiration'));
        $tokenResult = $user->createToken('API Access Token', ['*'], $expiration);

        return $tokenResult->plainTextToken;
    }


    public function revokeAccess($userId): void
    {
        $user = $this->userService->getUserById($userId);
        $user->tokens()->delete();
    }

    public function renewAccessToken($refreshToken): ?string
    {
        $refreshTokenModel = $this->getRefreshByHash($refreshToken);

        if ($refreshTokenModel) {
            $user = $refreshTokenModel->user;
            $this->revokeAccess($user->getAuthIdentifier());
            return $this->createAccess($user->getAuthIdentifier());
        }

        return null;
    }
}
