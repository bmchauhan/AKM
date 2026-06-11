<?php

namespace App\Services\Admin;

use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RoleService
{
    public function __construct(
        private readonly RoleRepositoryInterface $roles,
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
                'users_count' => $role->users()->count(),
            ])
            ->values()
            ->all();
    }

    public function sync(array $roles, array $deletedIds): void
    {
        DB::transaction(function () use ($roles, $deletedIds) {
            foreach ($deletedIds as $id) {
                $this->deleteRole((int) $id);
            }

            foreach ($roles as $roleData) {
                if (! empty($roleData['id'])) {
                    $this->updateRole((int) $roleData['id'], $roleData);
                } else {
                    $this->createRole($roleData);
                }
            }
        });
    }

    private function createRole(array $data): void
    {
        $this->roles->create([
            'name' => trim($data['name']),
            'short_form' => $this->normalizeShortForm($data['short_form'] ?? $data['name']),
            'slug' => $this->normalizeSlug($data['slug'] ?? $data['name']),
            'description' => filled($data['description'] ?? null) ? trim($data['description']) : null,
            'is_system' => false,
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

        if (! $role->is_system) {
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

        if ($role->is_system) {
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
