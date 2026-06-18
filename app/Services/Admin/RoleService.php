<?php

namespace App\Services\Admin;

use App\Enums\RoleType;
use App\Enums\UserRole;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RoleService
{
    public function __construct(
        private readonly RoleRepositoryInterface $roles,
        private readonly CommitteeRoleRegistry $committeeRoles,
    ) {}

    public function listForScreen(): array
    {
        return $this->roles->all()
            ->map(fn ($role) => [
                'id' => $role->id,
                'name' => $role->name,
                'short_form' => $role->short_form,
                'slug' => $role->slug,
                'description' => $role->description ?? '',
                'is_system' => $role->is_system,
                'role_type' => $role->role_type?->value ?? RoleType::Committee->value,
                'role_type_label' => $role->role_type?->label() ?? RoleType::Committee->label(),
                'is_leadership' => (bool) $role->is_leadership,
                'is_super_admin' => $role->isSuperAdminRole(),
                'users_count' => $this->roles->usersCount($role->slug),
                'slug_locked' => $this->roles->usersCount($role->slug) > 0,
                'can_assign_user' => $role->isCommitteeRole() || $role->isSuperAdminRole(),
            ])
            ->values()
            ->all();
    }

    public function sync(array $roles, array $deletedIds): array
    {
        $createdSlugs = [];

        DB::transaction(function () use ($roles, $deletedIds, &$createdSlugs) {
            foreach ($deletedIds as $id) {
                $this->deleteRole((int) $id);
            }

            foreach ($roles as $roleData) {
                if (! empty($roleData['id'])) {
                    $this->updateRole((int) $roleData['id'], $roleData);
                } else {
                    $created = $this->createRole($roleData);
                    $createdSlugs[] = $created->slug;
                }
            }
        });

        $this->committeeRoles->flush();

        return $createdSlugs;
    }

    private function createRole(array $data): \App\Models\Role
    {
        if (($data['role_type'] ?? RoleType::Committee->value) === RoleType::SuperAdmin->value) {
            throw ValidationException::withMessages([
                'roles' => [__('messages.roles_super_admin_create_forbidden')],
            ]);
        }

        return $this->roles->create([
            'name' => trim($data['name']),
            'short_form' => $this->normalizeShortForm($data['short_form'] ?? $data['name']),
            'slug' => $this->normalizeSlug($data['slug'] ?? $data['name']),
            'description' => filled($data['description'] ?? null) ? trim($data['description']) : null,
            'is_system' => false,
            'role_type' => RoleType::Committee->value,
            'is_leadership' => (bool) ($data['is_leadership'] ?? false),
        ]);
    }

    private function updateRole(int $id, array $data): void
    {
        $role = $this->roles->findById($id);

        if (! $role) {
            throw ValidationException::withMessages([
                'roles' => [__('messages.roles_not_found')],
            ]);
        }

        $payload = [
            'name' => trim($data['name']),
            'short_form' => $this->normalizeShortForm($data['short_form'] ?? $data['name']),
            'description' => filled($data['description'] ?? null) ? trim($data['description']) : null,
        ];

        if ($role->isCommitteeRole()) {
            $payload['is_leadership'] = (bool) ($data['is_leadership'] ?? false);
        }

        $usersAssigned = $this->roles->usersCount($role->slug) > 0;

        if (! $role->is_system && ! $usersAssigned) {
            $payload['slug'] = $this->normalizeSlug($data['slug'] ?? $data['name']);
        }

        $this->roles->update($role, $payload);
    }

    private function deleteRole(int $id): void
    {
        $role = $this->roles->findById($id);

        if (! $role) {
            return;
        }

        if ($role->is_system || $role->isSuperAdminRole()) {
            throw ValidationException::withMessages([
                'deleted_ids' => [__('messages.roles_system_delete')],
            ]);
        }

        if ($this->roles->usersCount($role->slug) > 0) {
            throw ValidationException::withMessages([
                'deleted_ids' => [__('messages.roles_in_use_delete', ['name' => $role->name])],
            ]);
        }

        $this->roles->delete($role);
    }

    private function normalizeSlug(string $value): string
    {
        return Str::slug($value, '_');
    }

    private function normalizeShortForm(string $value): string
    {
        $normalized = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $value) ?? '');

        if ($normalized === '') {
            $normalized = collect(preg_split('/\s+/', trim($value)) ?: [])
                ->filter()
                ->map(fn (string $word) => strtoupper(substr($word, 0, 1)))
                ->implode('');
        }

        return substr($normalized, 0, 20);
    }
}
