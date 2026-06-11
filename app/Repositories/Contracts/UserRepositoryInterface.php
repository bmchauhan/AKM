<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface
{
    public function findByEmailOrUsername(string $login): ?User;

    public function create(array $data): User;

    public function emailExists(string $email): bool;

    public function usernameExists(string $username): bool;
}
