<?php

namespace Database\Seeders;

use App\Models\Module;
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
                'slug' => 'houses',
                'name' => 'Houses',
                'description' => 'House registry, ownership history, and ownership transfers.',
                'is_system' => true,
                'sort_order' => 3,
            ],
            [
                'slug' => 'finance',
                'name' => 'Finance',
                'description' => 'Society fund balance, maintenance collections, and expenses.',
                'is_system' => true,
                'sort_order' => 4,
            ],
            [
                'slug' => 'workers',
                'name' => 'Workers',
                'description' => 'Sweeper, garbage collector, and security guard profiles with salary history.',
                'is_system' => true,
                'sort_order' => 5,
            ],
            [
                'slug' => 'settings',
                'name' => 'Settings',
                'description' => 'Admin configuration including roles, modules, and permissions.',
                'is_system' => true,
                'sort_order' => 6,
            ],
            [
                'slug' => 'landing_page',
                'name' => 'Landing Page',
                'description' => 'Manage public website content and landing page sections.',
                'is_system' => true,
                'sort_order' => 7,
            ],
        ];

        foreach ($modules as $module) {
            Module::query()->updateOrCreate(
                ['slug' => $module['slug']],
                [
                    ...$module,
                    'is_permission_target' => in_array($module['slug'], ['workers'], true),
                ],
            );
        }
    }
}
