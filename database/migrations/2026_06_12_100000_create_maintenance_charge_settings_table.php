<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_charge_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('monthly_amount', 12, 2);
            $table->date('effective_from');
            $table->date('end_date')->nullable();
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('set_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'effective_from']);
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_charge_settings');
    }
};
