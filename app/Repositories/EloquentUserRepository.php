<?php

namespace App\Repositories;

use App\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Sanctum\PersonalAccessToken;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function all(): Collection
    {
        return User::all();
    }

    public function create(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        return User::create($data);
    }

    public function auth(array $data): ?User
    {
        $user = $this->findByEmail($data['email']);
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return null;
        }
        return $user;
    }

    public function findById($userId): ?User
    {
        return User::where('id', $userId)->firstOrFail();
    }

    public function findByEmail($email): ?User
    {
        return User::where('email', $email)->firstOrFail();
    }


    public function update(User $user, array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        $user->update($data);
        return $user;
    }

    public function delete($userId): bool
    {
        $user = $this->findById($userId);
        if ($user) {
            PersonalAccessToken::where('tokenable_id', $userId)
                ->where('tokenable_type', User::class)
                ->delete();
            return $user->delete();
        }
        return false;
    }

}
