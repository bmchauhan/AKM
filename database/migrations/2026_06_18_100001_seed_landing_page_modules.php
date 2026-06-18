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
        $parent = Module::query()->updateOrCreate(
            ['slug' => 'landing_page'],
            [
                'name' => 'Landing Page',
                'description' => 'Manage public website content and landing page sections.',
                'is_system' => true,
                'is_permission_target' => false,
                'sort_order' => 6,
            ],
        );

        $child = Module::query()->updateOrCreate(
            ['slug' => 'landing_page_useful_directory'],
            [
                'parent_id' => $parent->id,
                'name' => 'Useful Directory',
                'description' => 'Public contact directory for services, government, emergency, and committee contacts.',
                'is_system' => true,
                'is_permission_target' => true,
                'sort_order' => 1,
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
                'can_create' => true,
                'can_read' => true,
                'can_update' => true,
                'can_delete' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        $child = Module::query()->where('slug', 'landing_page_useful_directory')->first();

        if ($child) {
            DB::table('module_role')->where('module_id', $child->id)->delete();
            $child->delete();
        }

        Module::query()->where('slug', 'landing_page')->delete();
    }
};
