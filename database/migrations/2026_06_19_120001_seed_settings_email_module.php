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
        $parent = Module::query()->where('slug', 'settings')->first();

        if (! $parent) {
            return;
        }

        $child = Module::query()->updateOrCreate(
            ['slug' => 'settings_email'],
            [
                'parent_id' => $parent->id,
                'name' => 'Email',
                'description' => 'Configure outbound email delivery and review email logs.',
                'is_system' => true,
                'is_permission_target' => true,
                'sort_order' => 4,
            ],
        );

        $superAdmin = Role::query()->where('slug', UserRole::SuperAdmin->value)->first();

        if (! $superAdmin) {
            return;
        }

        DB::table('module_role')->updateOrInsert(
            [
                'module_id' => $child->id,
                'role_id' => $superAdmin->id,
            ],
            [
                'can_create' => false,
                'can_read' => true,
                'can_update' => true,
                'can_delete' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        $child = Module::query()->where('slug', 'settings_email')->first();

        if ($child) {
            DB::table('module_role')->where('module_id', $child->id)->delete();
            $child->delete();
        }
    }
};
