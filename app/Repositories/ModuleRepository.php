<?php

namespace App\Repositories;

use App\Enums\ModulePermissionAction;
use App\Models\Module;
use App\Models\Role;
use App\Repositories\Contracts\ModuleRepositoryInterface;
use Illuminate\Support\Collection;

class ModuleRepository implements ModuleRepositoryInterface
{
    public function allOrdered(): Collection
    {
        return Module::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function findBySlug(string $slug): ?Module
    {
        return Module::query()->where('slug', $slug)->first();
    }

    public function roleCanOnModule(string $roleSlug, string $moduleSlug, ModulePermissionAction|string $action): bool
    {
        $action = $action instanceof ModulePermissionAction
            ? $action
            : ModulePermissionAction::from($action);

        return Module::query()
            ->where('slug', $moduleSlug)
            ->whereHas('roles', function ($query) use ($roleSlug, $action) {
                $query->where('slug', $roleSlug)
                    ->where($action->column(), true);
            })
            ->exists();
    }

    public function syncRolePermissions(int $roleId, array $permissions): void
    {
        $role = Role::query()->findOrFail($roleId);
        $syncData = [];

        foreach ($permissions as $permission) {
            $moduleId = (int) ($permission['module_id'] ?? 0);
            $flags = [
                'can_create' => (bool) ($permission['can_create'] ?? false),
                'can_read' => (bool) ($permission['can_read'] ?? false),
                'can_update' => (bool) ($permission['can_update'] ?? false),
                'can_delete' => (bool) ($permission['can_delete'] ?? false),
            ];

            if (in_array(true, $flags, true)) {
                $syncData[$moduleId] = $flags;
            }
        }

        $role->modules()->sync($syncData);
    }
}
