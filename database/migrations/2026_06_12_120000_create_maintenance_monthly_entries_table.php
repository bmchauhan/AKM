<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_monthly_entries', function (Blueprint $table) {
            $table->id();
            $table->date('billing_month');
            $table->foreignId('main_member_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('maintenance_charge_setting_id')->nullable();
            $table->decimal('charge_amount', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->string('status', 20)->default('pending');
            $table->date('paid_on')->nullable();
            $table->string('payment_mode', 30)->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('recorded_by_user_id')->nullable();
            $table->timestamps();

            $table->foreign('maintenance_charge_setting_id', 'maint_monthly_charge_setting_fk')
                ->references('id')
                ->on('maintenance_charge_settings')
                ->nullOnDelete();
            $table->foreign('recorded_by_user_id', 'maint_monthly_recorded_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->unique(['main_member_id', 'billing_month'], 'maint_monthly_member_month_uq');
            $table->index(['billing_month', 'status'], 'maint_monthly_billing_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_monthly_entries');
    }
};
