<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class SubModuleSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            'users' => [
                ['slug' => 'users_all', 'name_key' => 'users_all', 'sort_order' => 1],
                ['slug' => 'users_add', 'name_key' => 'users_add', 'sort_order' => 2],
            ],
            'members' => [
                ['slug' => 'members_all', 'name_key' => 'members_all', 'sort_order' => 1],
                ['slug' => 'members_add', 'name_key' => 'members_add', 'sort_order' => 2],
            ],
            'houses' => [
                ['slug' => 'houses_all', 'name_key' => 'houses_all', 'sort_order' => 1],
                ['slug' => 'houses_transfer', 'name_key' => 'houses_transfer', 'sort_order' => 2],
            ],
            'finance' => [
                ['slug' => 'finance_overview', 'name_key' => 'finance_overview', 'sort_order' => 1],
                ['slug' => 'finance_house_ledger', 'name_key' => 'finance_house_ledger', 'sort_order' => 2],
                ['slug' => 'finance_maintenance_ledger', 'name_key' => 'finance_maintenance_ledger', 'sort_order' => 3],
                ['slug' => 'finance_collections', 'name_key' => 'finance_collections', 'sort_order' => 4],
                ['slug' => 'finance_expenses', 'name_key' => 'finance_expenses', 'sort_order' => 5],
                ['slug' => 'finance_maintenance_charges', 'name_key' => 'finance_maintenance_charges', 'sort_order' => 6],
                ['slug' => 'finance_fund_setting', 'name_key' => 'finance_fund_setting', 'sort_order' => 7],
            ],
            'visitors' => [
                ['slug' => 'visitors_log', 'name_key' => 'visitors_log', 'sort_order' => 1],
                ['slug' => 'visitors_all', 'name_key' => 'visitors_all', 'sort_order' => 2],
            ],
            'settings' => [
                ['slug' => 'settings_modules', 'name_key' => 'settings_modules', 'sort_order' => 1],
                ['slug' => 'settings_permissions', 'name_key' => 'settings_permissions', 'sort_order' => 2],
                ['slug' => 'settings_roles', 'name_key' => 'settings_roles', 'sort_order' => 3],
                ['slug' => 'settings_email', 'name_key' => 'settings_email', 'sort_order' => 4],
            ],
            'landing_page' => [
                ['slug' => 'landing_page_directory_roles', 'name_key' => 'directory_roles', 'sort_order' => 1],
                ['slug' => 'landing_page_useful_directory', 'name_key' => 'useful_directory', 'sort_order' => 2],
            ],
        ];

        foreach ($definitions as $parentSlug => $children) {
            $parent = Module::query()->where('slug', $parentSlug)->first();

            if (! $parent) {
                continue;
            }

            foreach ($children as $child) {
                Module::query()->updateOrCreate(
                    ['slug' => $child['slug']],
                    [
                        'parent_id' => $parent->id,
                        'name' => __("messages.{$child['name_key']}"),
                        'description' => __('messages.modules_sub_description', [
                            'parent' => $parent->name,
                        ]),
                        'is_system' => true,
                        'is_permission_target' => true,
                        'sort_order' => $child['sort_order'],
                    ],
                );
            }
        }

        $this->command?->info('Sub-modules seeded for Users, Members, Houses, Finance, Visitors, Settings, and Landing Page.');
    }
}
