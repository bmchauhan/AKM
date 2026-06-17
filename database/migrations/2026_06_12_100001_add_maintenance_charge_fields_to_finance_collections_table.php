<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_collections', function (Blueprint $table) {
            $table->foreignId('maintenance_charge_setting_id')
                ->nullable()
                ->after('main_member_id')
                ->constrained('maintenance_charge_settings')
                ->nullOnDelete();
            $table->decimal('maintenance_base_amount', 12, 2)->nullable()->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('finance_collections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('maintenance_charge_setting_id');
            $table->dropColumn('maintenance_base_amount');
        });
    }
};
