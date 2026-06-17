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
                'slug' => 'finance',
                'name' => 'Finance',
                'description' => 'Society fund balance, maintenance collections, and expenses.',
                'is_system' => true,
                'sort_order' => 3,
            ],
            [
                'slug' => 'workers',
                'name' => 'Workers',
                'description' => 'Sweeper, garbage collector, and security guard profiles with salary history.',
                'is_system' => true,
                'sort_order' => 4,
            ],
            [
                'slug' => 'settings',
                'name' => 'Settings',
                'description' => 'Admin configuration including roles, modules, and permissions.',
                'is_system' => true,
                'sort_order' => 5,
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
        $financeModule = Module::query()->where('slug', 'finance')->firstOrFail();
        $workersModule = Module::query()->where('slug', 'workers')->firstOrFail();
        $settingsModule = Module::query()->where('slug', 'settings')->firstOrFail();
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

        $superAdmin = Role::query()->where('slug', UserRole::SuperAdmin->value)->first();
        $ccm = Role::query()->where('slug', 'chief_committee_member')->first();
        $vccm = Role::query()->where('slug', 'vice_chief_committee_member')->first();
        $fcm = Role::query()->where('slug', 'finance_committee_member')->first();
        $cm = Role::query()->where('slug', 'committee_member')->first();

        if ($superAdmin) {
            $superAdmin->modules()->sync([
                $usersModule->id => $fullAccess,
                $membersModule->id => $fullAccess,
                $financeModule->id => $fullAccess,
                $workersModule->id => $fullAccess,
                $settingsModule->id => $fullAccess,
            ]);
        }

        if ($ccm) {
            $ccm->modules()->syncWithoutDetaching([
                $usersModule->id => $fullAccess,
                $membersModule->id => $fullAccess,
                $financeModule->id => $fullAccess,
                $workersModule->id => $fullAccess,
            ]);
        }

        if ($vccm) {
            $vccm->modules()->syncWithoutDetaching([
                $usersModule->id => $fullAccess,
                $membersModule->id => $fullAccess,
                $financeModule->id => $fullAccess,
                $workersModule->id => $fullAccess,
            ]);
        }

        if ($fcm) {
            $fcm->modules()->syncWithoutDetaching([
                $financeModule->id => $fullAccess,
                $workersModule->id => $fullAccess,
            ]);
        }

        if ($cm) {
            $cm->modules()->syncWithoutDetaching([
                $financeModule->id => $readCreate,
            ]);
        }
    }
}
