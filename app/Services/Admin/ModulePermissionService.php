<?php

namespace App\Services\Admin;

use App\Enums\ModulePermissionAction;
use App\Enums\UserRole;
use App\Models\Module;
use App\Models\Role;
use App\Repositories\Contracts\ModuleRepositoryInterface;
use App\Support\SuperAdminOnlyModules;
use Illuminate\Support\Facades\DB;

class ModulePermissionService
{
    public function __construct(
        private readonly ModuleRepositoryInterface $modules,
    ) {}

    public function screenData(): array
    {
        $parents = Module::query()
            ->with(['children' => fn ($query) => $query->where('is_permission_target', true)->orderBy('sort_order')->orderBy('name')])
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $permissionModules = Module::query()
            ->where('is_permission_target', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $moduleGroups = $parents->map(function (Module $parent) {
            $children = $parent->is_permission_target
                ? collect([$parent])
                : $parent->children;

            return [
                'parent' => [
                    'id' => $parent->id,
                    'slug' => $parent->slug,
                    'name' => $parent->name,
                    'description' => $parent->description ?? '',
                    'is_group_header' => ! $parent->is_permission_target,
                ],
                'children' => $children->map(fn (Module $child) => $this->mapPermissionModule($child))->values()->all(),
            ];
        })->filter(fn (array $group) => $group['children'] !== [])->values()->all();

        $roles = Role::query()
            ->orderBy('name')
            ->get(['id', 'name', 'short_form', 'slug', 'is_system']);

        return [
            'modules' => $permissionModules->map(fn (Module $module) => $this->mapPermissionModule($module))->values()->all(),
            'module_groups' => $moduleGroups,
            'roles' => $roles->map(fn ($role) => [
                'id' => $role->id,
                'name' => $role->name,
                'short_form' => $role->short_form,
                'slug' => $role->slug,
                'is_system' => $role->is_system,
                'is_super_admin' => $role->slug === UserRole::SuperAdmin->value,
            ])->values()->all(),
            'permissions' => $this->loadPermissionsMap(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPermissionModule(Module $module): array
    {
        return [
            'id' => $module->id,
            'slug' => $module->slug,
            'name' => $module->name,
            'description' => $module->description ?? '',
            'is_system' => $module->is_system,
            'settings_only' => SuperAdminOnlyModules::isSuperAdminOnlySlug($module->slug),
        ];
    }

    public function syncForRole(int $roleId, array $permissions): void
    {
        $role = Role::query()->findOrFail($roleId);

        if ($role->slug === UserRole::SuperAdmin->value) {
            $this->syncSuperAdminPermissions($role);

            return;
        }

        $this->revokeSuperAdminOnlyModulesFromRole($role);

        $normalized = collect($permissions)
            ->map(function (array $permission) use ($role) {
                $module = Module::query()->find((int) ($permission['module_id'] ?? 0));

                if ($module && SuperAdminOnlyModules::isSuperAdminOnlySlug($module->slug) && $role->slug !== UserRole::SuperAdmin->value) {
                    return [
                        'module_id' => $module->id,
                        'can_create' => false,
                        'can_read' => false,
                        'can_update' => false,
                        'can_delete' => false,
                    ];
                }

                return [
                    'module_id' => (int) ($permission['module_id'] ?? 0),
                    'can_create' => (bool) ($permission['can_create'] ?? false),
                    'can_read' => (bool) ($permission['can_read'] ?? false),
                    'can_update' => (bool) ($permission['can_update'] ?? false),
                    'can_delete' => (bool) ($permission['can_delete'] ?? false),
                ];
            })
            ->all();

        $this->modules->syncRolePermissions($roleId, $normalized);
    }

    public function roleCanOnModule(string $roleSlug, string $moduleSlug, ModulePermissionAction|string $action): bool
    {
        if ($roleSlug === UserRole::SuperAdmin->value) {
            return true;
        }

        if (SuperAdminOnlyModules::isSuperAdminOnlySlug($moduleSlug)) {
            return false;
        }

        return $this->modules->roleCanOnModule($roleSlug, $moduleSlug, $action);
    }

    public function roleCanOnModuleGroup(string $roleSlug, string $parentSlug, ModulePermissionAction|string $action): bool
    {
        if ($roleSlug === UserRole::SuperAdmin->value) {
            return true;
        }

        if (SuperAdminOnlyModules::isSuperAdminOnlySlug($parentSlug)) {
            return false;
        }

        $parent = Module::query()->where('slug', $parentSlug)->whereNull('parent_id')->first();

        if (! $parent) {
            return false;
        }

        if ($parent->is_permission_target) {
            return $this->roleCanOnModule($roleSlug, $parentSlug, $action);
        }

        return $parent->children()
            ->where('is_permission_target', true)
            ->pluck('slug')
            ->contains(fn (string $childSlug) => $this->roleCanOnModule($roleSlug, $childSlug, $action));
    }

    private function syncSuperAdminPermissions(Role $role): void
    {
        $allFlags = [
            'can_create' => true,
            'can_read' => true,
            'can_update' => true,
            'can_delete' => true,
        ];

        $syncData = Module::query()
            ->where('is_permission_target', true)
            ->pluck('id')
            ->mapWithKeys(fn ($id) => [(int) $id => $allFlags])
            ->all();

        $role->modules()->sync($syncData);
    }

    public function revokeSuperAdminOnlyModulesFromNonSuperAdmins(): void
    {
        $superAdmin = Role::query()->where('slug', UserRole::SuperAdmin->value)->first();

        Role::query()
            ->when($superAdmin, fn ($query) => $query->where('id', '!=', $superAdmin->id))
            ->each(fn (Role $role) => $this->revokeSuperAdminOnlyModulesFromRole($role));
    }

    private function revokeSuperAdminOnlyModulesFromRole(Role $role): void
    {
        if ($role->slug === UserRole::SuperAdmin->value) {
            return;
        }

        $moduleIds = Module::query()
            ->where('is_permission_target', true)
            ->get(['id', 'slug'])
            ->filter(fn (Module $module) => SuperAdminOnlyModules::isSuperAdminOnlySlug($module->slug))
            ->pluck('id');

        if ($moduleIds->isEmpty()) {
            return;
        }

        $role->modules()->detach($moduleIds);
    }

    private function loadPermissionsMap(): array
    {
        $map = [];

        DB::table('module_role')
            ->get()
            ->each(function ($row) use (&$map) {
                $map[$row->role_id][$row->module_id] = [
                    'can_create' => (bool) $row->can_create,
                    'can_read' => (bool) $row->can_read,
                    'can_update' => (bool) $row->can_update,
                    'can_delete' => (bool) $row->can_delete,
                ];
            });

        return $map;
    }
}
