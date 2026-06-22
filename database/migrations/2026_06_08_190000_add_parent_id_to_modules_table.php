<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('modules', 'parent_id')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->foreignId('parent_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('modules')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('modules', 'is_permission_target')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->boolean('is_permission_target')->default(true)->after('is_system');
            });

            DB::table('modules')->update(['is_permission_target' => true]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('modules', 'parent_id')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->dropConstrainedForeignId('parent_id');
            });
        }

        if (Schema::hasColumn('modules', 'is_permission_target')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->dropColumn('is_permission_target');
            });
        }
    }
};
