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
        $parent = Module::query()->where('slug', 'landing_page')->first();

        if (! $parent) {
            return;
        }

        $child = Module::query()->updateOrCreate(
            ['slug' => 'landing_page_directory_roles'],
            [
                'parent_id' => $parent->id,
                'name' => 'Directory Roles',
                'description' => 'Manage useful directory categories with multilingual titles.',
                'is_system' => true,
                'is_permission_target' => true,
                'sort_order' => 0,
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

        Module::query()
            ->where('slug', 'landing_page_useful_directory')
            ->update(['sort_order' => 2]);
    }

    public function down(): void
    {
        $child = Module::query()->where('slug', 'landing_page_directory_roles')->first();

        if ($child) {
            DB::table('module_role')->where('module_id', $child->id)->delete();
            $child->delete();
        }

        Module::query()
            ->where('slug', 'landing_page_useful_directory')
            ->update(['sort_order' => 1]);
    }
};
