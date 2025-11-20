<?php

namespace App\Services;

use App\Contracts\UserRepositoryInterface;
use App\Contracts\UserServiceInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserService implements UserServiceInterface
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function createUser(array $data): ?User
    {
        return $this->userRepository->create($data);
    }

    public function authUser(array $data): ?User
    {
        return $this->userRepository->auth($data);
    }

    public function updateUser($userId, array $data): ?User
    {
        $user = $this->userRepository->findById($userId);
        return $this->userRepository->update($user, $data);
    }

    public function deleteUser($userId): ?bool
    {
        return $this->userRepository->delete($userId);
    }

    public function getAllUser(): Collection
    {
        return $this->userRepository->all();
    }

    public function getUserById($userId): ?User
    {
        return $this->userRepository->findById($userId);
    }

}
