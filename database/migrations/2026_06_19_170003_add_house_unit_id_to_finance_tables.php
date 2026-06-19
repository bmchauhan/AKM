<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_collections', function (Blueprint $table) {
            $table->foreignId('house_unit_id')->nullable()->after('main_member_id')->constrained('house_units')->nullOnDelete();
            $table->index('house_unit_id', 'finance_collections_house_unit_idx');
        });

        Schema::table('maintenance_monthly_entries', function (Blueprint $table) {
            $table->foreignId('house_unit_id')->nullable()->after('main_member_id')->constrained('house_units')->nullOnDelete();
            $table->index('house_unit_id', 'maint_monthly_house_unit_idx');
        });
    }

    public function down(): void
    {
        Schema::table('finance_collections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('house_unit_id');
        });

        Schema::table('maintenance_monthly_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('house_unit_id');
        });
    }
};
