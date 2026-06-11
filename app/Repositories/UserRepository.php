<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function findByEmailOrUsername(string $login): ?User
    {
        return User::query()
            ->where('email', $login)
            ->orWhere('username', $login)
            ->first();
    }

    public function create(array $data): User
    {
        return User::query()->create($data);
    }

    public function emailExists(string $email): bool
    {
        return User::query()->where('email', $email)->exists();
    }

    public function usernameExists(string $username): bool
    {
        return User::query()->where('username', $username)->exists();
    }
}
