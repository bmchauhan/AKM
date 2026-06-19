<?php

use App\Enums\UserRole;
use App\Models\Module;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Module::query()
            ->whereIn('slug', ['finance', 'workers', 'settings', 'landing_page'])
            ->where('sort_order', '>=', 3)
            ->each(function (Module $module): void {
                $module->update(['sort_order' => $module->sort_order + 1]);
            });

        $parent = Module::query()->updateOrCreate(
            ['slug' => 'houses'],
            [
                'name' => 'Houses',
                'description' => 'House registry, ownership history, and ownership transfers.',
                'is_system' => true,
                'is_permission_target' => false,
                'sort_order' => 3,
            ],
        );

        $all = Module::query()->updateOrCreate(
            ['slug' => 'houses_all'],
            [
                'parent_id' => $parent->id,
                'name' => 'All Houses',
                'description' => 'Browse house units, current owners, and ownership timeline.',
                'is_system' => true,
                'is_permission_target' => true,
                'sort_order' => 1,
            ],
        );

        $transfer = Module::query()->updateOrCreate(
            ['slug' => 'houses_transfer'],
            [
                'parent_id' => $parent->id,
                'name' => 'Transfer Ownership',
                'description' => 'Transfer house ownership to a new main member.',
                'is_system' => true,
                'is_permission_target' => true,
                'sort_order' => 2,
            ],
        );

        $fullAccess = [
            'can_create' => true,
            'can_read' => true,
            'can_update' => true,
            'can_delete' => true,
        ];

        $rolesWithHouses = Role::query()
            ->whereIn('slug', [
                UserRole::SuperAdmin->value,
                'chief_committee_member',
                'vice_chief_committee_member',
            ])
            ->get();

        foreach ($rolesWithHouses as $role) {
            foreach ([$all, $transfer] as $module) {
                DB::table('module_role')->updateOrInsert(
                    [
                        'module_id' => $module->id,
                        'role_id' => $role->id,
                    ],
                    array_merge($fullAccess, [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]),
                );
            }
        }
    }

    public function down(): void
    {
        foreach (['houses_transfer', 'houses_all'] as $slug) {
            $module = Module::query()->where('slug', $slug)->first();

            if ($module) {
                DB::table('module_role')->where('module_id', $module->id)->delete();
                $module->delete();
            }
        }

        Module::query()->where('slug', 'houses')->delete();

        Module::query()
            ->whereIn('slug', ['finance', 'workers', 'settings', 'landing_page'])
            ->where('sort_order', '>', 3)
            ->each(function (Module $module): void {
                $module->update(['sort_order' => $module->sort_order - 1]);
            });
    }
};
