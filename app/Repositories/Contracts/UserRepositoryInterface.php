<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    /**
     * @param  array{house_type?: string, house_number?: string, name?: string, role?: string, exclude_super_admin?: bool}  $filters
     */
    public function paginatedForAdmin(array $filters = [], int $perPage = 25): LengthAwarePaginator;

    public function findById(int $id): ?User;

    public function findByEmailOrUsername(string $login): ?User;

    public function create(array $data): User;

    public function update(User $user, array $data): User;

    public function delete(User $user): void;

    public function emailExists(string $email, ?int $exceptUserId = null): bool;

    public function usernameExists(string $username, ?int $exceptUserId = null): bool;

    public function householdMembersForMainMember(int $mainMemberId): Collection;

    public function mainMembersForSelect(): Collection;
}
