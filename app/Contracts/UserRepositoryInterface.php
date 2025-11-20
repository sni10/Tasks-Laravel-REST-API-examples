<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    public function all(): Collection;
    public function create(array $data): ?User;
    public function findById($userId): ?User;
    public function findByEmail($email): ?User;
    public function update(User $user, array $data): ?User;
    public function delete($userId): ?bool;

}
