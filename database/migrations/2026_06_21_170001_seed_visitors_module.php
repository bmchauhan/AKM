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
            ->whereIn('slug', ['settings', 'landing_page'])
            ->where('sort_order', '>=', 6)
            ->each(function (Module $module): void {
                $module->update(['sort_order' => $module->sort_order + 1]);
            });

        $parent = Module::query()->updateOrCreate(
            ['slug' => 'visitors'],
            [
                'name' => 'Visitors',
                'description' => 'Gate visitor logging, rental compliance, and visit history.',
                'is_system' => true,
                'is_permission_target' => false,
                'sort_order' => 6,
            ],
        );

        $log = Module::query()->updateOrCreate(
            ['slug' => 'visitors_log'],
            [
                'parent_id' => $parent->id,
                'name' => 'Log Visitor',
                'description' => 'Record visitor entry and checkout at the security gate.',
                'is_system' => true,
                'is_permission_target' => true,
                'sort_order' => 1,
            ],
        );

        $all = Module::query()->updateOrCreate(
            ['slug' => 'visitors_all'],
            [
                'parent_id' => $parent->id,
                'name' => 'All Visitors',
                'description' => 'Browse and manage visitor entry history.',
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

        $logAccess = [
            'can_create' => true,
            'can_read' => true,
            'can_update' => true,
            'can_delete' => false,
        ];

        $rolesWithFull = Role::query()
            ->whereIn('slug', [
                UserRole::SuperAdmin->value,
                'chief_committee_member',
                'vice_chief_committee_member',
            ])
            ->get();

        foreach ($rolesWithFull as $role) {
            DB::table('module_role')->updateOrInsert(
                ['module_id' => $all->id, 'role_id' => $role->id],
                array_merge($fullAccess, ['created_at' => now(), 'updated_at' => now()]),
            );
            DB::table('module_role')->updateOrInsert(
                ['module_id' => $log->id, 'role_id' => $role->id],
                array_merge($logAccess, ['created_at' => now(), 'updated_at' => now()]),
            );
        }
    }

    public function down(): void
    {
        foreach (['visitors_all', 'visitors_log'] as $slug) {
            $module = Module::query()->where('slug', $slug)->first();

            if ($module) {
                DB::table('module_role')->where('module_id', $module->id)->delete();
                $module->delete();
            }
        }

        Module::query()->where('slug', 'visitors')->delete();

        Module::query()
            ->whereIn('slug', ['settings', 'landing_page'])
            ->where('sort_order', '>', 6)
            ->each(function (Module $module): void {
                $module->update(['sort_order' => $module->sort_order - 1]);
            });
    }
};
