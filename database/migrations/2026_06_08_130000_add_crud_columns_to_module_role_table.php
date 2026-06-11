<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('module_role', function (Blueprint $table) {
            $table->boolean('can_create')->default(false)->after('role_id');
            $table->boolean('can_read')->default(false)->after('can_create');
            $table->boolean('can_update')->default(false)->after('can_read');
            $table->boolean('can_delete')->default(false)->after('can_update');
        });

        DB::table('module_role')->update([
            'can_create' => true,
            'can_read' => true,
            'can_update' => true,
            'can_delete' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('module_role', function (Blueprint $table) {
            $table->dropColumn(['can_create', 'can_read', 'can_update', 'can_delete']);
        });
    }
};
