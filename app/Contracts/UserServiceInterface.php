<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserServiceInterface
{
    public function createUser(array $data): ?User;

    public function updateUser($userId, array $data): ?User;

    public function deleteUser($userId): ?bool;

    public function getAllUser(): Collection;

    public function getUserById($userId): ?User;

}
