<?php

use App\Enums\RoleType;
use App\Models\Module;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $role = Role::query()->updateOrCreate(
            ['slug' => 'security_guard'],
            [
                'name' => 'Security Guard',
                'short_form' => 'SG',
                'description' => 'Gate staff login for visitor logging at the security desk.',
                'is_system' => true,
                'role_type' => RoleType::Committee->value,
                'is_leadership' => false,
            ],
        );

        $visitorsLog = Module::query()->where('slug', 'visitors_log')->first();
        $visitorsAll = Module::query()->where('slug', 'visitors_all')->first();

        if (! $visitorsLog) {
            return;
        }

        $logAccess = [
            'can_create' => true,
            'can_read' => true,
            'can_update' => true,
            'can_delete' => false,
        ];

        DB::table('module_role')->updateOrInsert(
            ['module_id' => $visitorsLog->id, 'role_id' => $role->id],
            array_merge($logAccess, ['created_at' => now(), 'updated_at' => now()]),
        );

        if ($visitorsAll) {
            DB::table('module_role')->updateOrInsert(
                ['module_id' => $visitorsAll->id, 'role_id' => $role->id],
                [
                    'can_create' => false,
                    'can_read' => true,
                    'can_update' => false,
                    'can_delete' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    public function down(): void
    {
        $role = Role::query()->where('slug', 'security_guard')->first();

        if ($role) {
            DB::table('module_role')->where('role_id', $role->id)->delete();
            $role->delete();
        }
    }
};
