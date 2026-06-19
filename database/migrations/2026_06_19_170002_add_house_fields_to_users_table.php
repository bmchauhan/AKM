<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('house_unit_id')->nullable()->after('linked_main_member_id')->constrained('house_units')->nullOnDelete();
            $table->string('ownership_status', 20)->default('active')->after('house_unit_id');

            $table->index('ownership_status', 'users_ownership_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('house_unit_id');
            $table->dropIndex('users_ownership_status_idx');
            $table->dropColumn('ownership_status');
        });
    }
};
