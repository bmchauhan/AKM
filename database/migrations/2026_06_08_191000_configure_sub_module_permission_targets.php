<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const PARENT_SLUGS = [
        'users',
        'members',
        'finance',
        'settings',
    ];

    public function up(): void
    {
        DB::table('modules')
            ->whereIn('slug', self::PARENT_SLUGS)
            ->update(['is_permission_target' => false]);

        DB::table('modules')
            ->whereNotNull('parent_id')
            ->update(['is_permission_target' => true]);

        DB::table('modules')
            ->where('slug', 'workers')
            ->whereNull('parent_id')
            ->update(['is_permission_target' => true]);
    }

    public function down(): void
    {
        DB::table('modules')->update(['is_permission_target' => true]);
    }
};
