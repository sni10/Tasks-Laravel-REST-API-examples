<?php

namespace App\Contracts;

use App\Models\RefreshToken;
use App\Models\User;

interface TokenRepositoryInterface
{

    public function createAccess($userId): string;
    public function revokeAccess($userId): void;
    public function renewAccessToken($refreshToken): ?string;
    public function getRefreshByHash($refreshToken): ?RefreshToken;
    public function getRefreshById($tokenId): ?RefreshToken;
    public function createRefresh($userId): string;
    public function validateRefreshToken($refreshToken): bool;
    public function revokeRefreshToken($userId): void;
    public function isRefreshTokenExpired($refreshToken): bool;
    public function validateAccessToken($refreshToken): bool;

}
