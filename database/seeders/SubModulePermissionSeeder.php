<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Module;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubModulePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $this->migrateLegacyParentPermissionsToChildren();
        $this->seedDefaultRolePermissions();
    }

    private function migrateLegacyParentPermissionsToChildren(): void
    {
        $parents = Module::query()
            ->whereNull('parent_id')
            ->where('is_permission_target', false)
            ->with('children')
            ->get();

        foreach ($parents as $parent) {
            if ($parent->children->isEmpty()) {
                continue;
            }

            $parentPermissions = DB::table('module_role')
                ->where('module_id', $parent->id)
                ->get();

            foreach ($parentPermissions as $row) {
                $flags = [
                    'can_create' => (bool) $row->can_create,
                    'can_read' => (bool) $row->can_read,
                    'can_update' => (bool) $row->can_update,
                    'can_delete' => (bool) $row->can_delete,
                ];

                foreach ($parent->children as $child) {
                    if (! $child->is_permission_target) {
                        continue;
                    }

                    DB::table('module_role')->updateOrInsert(
                        [
                            'module_id' => $child->id,
                            'role_id' => $row->role_id,
                        ],
                        array_merge($flags, [
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]),
                    );
                }

                DB::table('module_role')->where('module_id', $parent->id)->delete();
            }
        }

        $this->command?->info('Expanded legacy parent module permissions onto sub-modules.');
    }

    private function seedDefaultRolePermissions(): void
    {
        if (DB::table('module_role')->exists()) {
            return;
        }

        $fullAccess = [
            'can_create' => true,
            'can_read' => true,
            'can_update' => true,
            'can_delete' => true,
        ];
        $readCreate = [
            'can_create' => true,
            'can_read' => true,
            'can_update' => false,
            'can_delete' => false,
        ];

        $roleMatrix = [
            UserRole::SuperAdmin->value => [
                'users' => $fullAccess,
                'members' => $fullAccess,
                'finance' => $fullAccess,
                'workers' => $fullAccess,
                'settings' => $fullAccess,
                'landing_page' => $fullAccess,
            ],
            'chief_committee_member' => [
                'users' => $fullAccess,
                'members' => $fullAccess,
                'finance' => $fullAccess,
                'workers' => $fullAccess,
            ],
            'vice_chief_committee_member' => [
                'users' => $fullAccess,
                'members' => $fullAccess,
                'finance' => $fullAccess,
                'workers' => $fullAccess,
            ],
            'finance_committee_member' => [
                'finance' => $fullAccess,
                'workers' => $fullAccess,
            ],
            'committee_member' => [
                'finance' => $readCreate,
            ],
        ];

        foreach ($roleMatrix as $roleSlug => $modules) {
            $role = Role::query()->where('slug', $roleSlug)->first();

            if (! $role) {
                continue;
            }

            $sync = [];

            foreach ($modules as $parentSlug => $flags) {
                $sync = array_merge($sync, $this->permissionIdsForParent($parentSlug, $flags));
            }

            if ($sync !== []) {
                $role->modules()->syncWithoutDetaching($sync);
            }
        }

        $this->command?->info('Default sub-module permissions seeded for committee roles.');
    }

    /**
     * @param  array{can_create: bool, can_read: bool, can_update: bool, can_delete: bool}  $flags
     * @return array<int, array{can_create: bool, can_read: bool, can_update: bool, can_delete: bool}>
     */
    private function permissionIdsForParent(string $parentSlug, array $flags): array
    {
        $parent = Module::query()->where('slug', $parentSlug)->first();

        if (! $parent) {
            return [];
        }

        $targets = $parent->children->isNotEmpty()
            ? $parent->children->where('is_permission_target', true)
            : collect([$parent])->filter(fn (Module $module) => $module->is_permission_target);

        $sync = [];

        foreach ($targets as $module) {
            $sync[$module->id] = $flags;
        }

        return $sync;
    }
}
