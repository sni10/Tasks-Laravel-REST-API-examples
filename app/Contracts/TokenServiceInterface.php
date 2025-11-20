<?php

namespace App\Contracts;

use App\Models\RefreshToken;

interface TokenServiceInterface
{
    public function createToken($userId): string;
    public function getRefreshModel($refreshToken): ?RefreshToken;

    public function revokeToken($userId): void;

    public function createRefresh($userId): string;

    public function revokeRefresh($userId): void;

    public function validateRefresh($refreshToken): bool;

    public function renewAccessToken($refreshToken): ?string;

}
