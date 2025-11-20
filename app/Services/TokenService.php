<?php

namespace App\Services;

use App\Contracts\TokenRepositoryInterface;
use App\Contracts\TokenServiceInterface;
use App\Models\RefreshToken;

class TokenService implements TokenServiceInterface
{
    protected TokenRepositoryInterface $tokenRepository;

    public function __construct(TokenRepositoryInterface $tokenRepository)
    {
        $this->tokenRepository = $tokenRepository;
    }

    public function createToken($userId): string
    {
        return $this->tokenRepository->createAccess($userId);
    }

    public function getRefreshModel($refreshToken): ?RefreshToken
    {
        return $this->tokenRepository->getRefreshByHash($refreshToken);
    }

    public function revokeToken($userId): void
    {
        $this->tokenRepository->revokeAccess($userId);
    }

    public function createRefresh($userId): string
    {
        return $this->tokenRepository->createRefresh($userId);
    }

    public function revokeRefresh($userId): void
    {
        $this->tokenRepository->revokeRefreshToken($userId);
    }

    public function validateRefresh($refreshToken): bool
    {
        return $this->tokenRepository->validateRefreshToken($refreshToken);
    }

    public function validateAccessToken($refreshToken): bool
    {
        return $this->tokenRepository->validateAccessToken($refreshToken);
    }

    public function renewAccessToken($refreshToken): ?string
    {
        return $this->tokenRepository->renewAccessToken($refreshToken);
    }

}
