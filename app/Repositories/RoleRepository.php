<?php

namespace App\Repositories;

use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Support\Collection;

class RoleRepository implements RoleRepositoryInterface
{
    public function all(): Collection
    {
        return Role::query()
            ->orderBy('is_system', 'desc')
            ->orderBy('name')
            ->get();
    }

    public function findById(int $id): ?Role
    {
        return Role::query()->find($id);
    }

    public function create(array $data): Role
    {
        return Role::query()->create($data);
    }

    public function update(Role $role, array $data): Role
    {
        $role->update($data);

        return $role->fresh();
    }

    public function delete(Role $role): void
    {
        $role->delete();
    }

    public function usersCount(string $slug): int
    {
        return \App\Models\User::query()
            ->where(function ($query) use ($slug) {
                $query->where('committee_role', $slug);

                if ($slug === \App\Enums\UserRole::SuperAdmin->value) {
                    $query->orWhere('role', $slug);
                }
            })
            ->count();
    }
}
