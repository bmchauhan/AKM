<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Module;
use App\Models\Role;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            [
                'slug' => 'users',
                'name' => 'Users',
                'description' => 'Manage society member accounts, profiles, and credentials.',
                'is_system' => true,
                'sort_order' => 1,
            ],
            [
                'slug' => 'members',
                'name' => 'Members',
                'description' => 'Manage family members linked to a main member household.',
                'is_system' => true,
                'sort_order' => 2,
            ],
            [
                'slug' => 'settings',
                'name' => 'Settings',
                'description' => 'Admin configuration including roles, modules, and permissions.',
                'is_system' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($modules as $module) {
            Module::query()->updateOrCreate(
                ['slug' => $module['slug']],
                $module,
            );
        }

        $this->seedDefaultPermissions();
    }

    private function seedDefaultPermissions(): void
    {
        $usersModule = Module::query()->where('slug', 'users')->firstOrFail();
        $membersModule = Module::query()->where('slug', 'members')->firstOrFail();
        $settingsModule = Module::query()->where('slug', 'settings')->firstOrFail();
        $fullAccess = [
            'can_create' => true,
            'can_read' => true,
            'can_update' => true,
            'can_delete' => true,
        ];

        $superAdmin = Role::query()->where('slug', UserRole::SuperAdmin->value)->first();
        $ccm = Role::query()->where('slug', 'chief_committee_member')->first();
        $vccm = Role::query()->where('slug', 'vice_chief_committee_member')->first();
        if ($superAdmin) {
            $superAdmin->modules()->sync([
                $usersModule->id => $fullAccess,
                $membersModule->id => $fullAccess,
                $settingsModule->id => $fullAccess,
            ]);
        }

        if ($ccm) {
            $ccm->modules()->syncWithoutDetaching([
                $usersModule->id => $fullAccess,
                $membersModule->id => $fullAccess,
            ]);
        }

        if ($vccm) {
            $vccm->modules()->syncWithoutDetaching([
                $usersModule->id => $fullAccess,
                $membersModule->id => $fullAccess,
            ]);
        }
    }
}
