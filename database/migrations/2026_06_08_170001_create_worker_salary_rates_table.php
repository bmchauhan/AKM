<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_salary_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->decimal('monthly_salary', 12, 2);
            $table->date('effective_from');
            $table->text('notes')->nullable();
            $table->foreignId('set_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['worker_id', 'effective_from']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_salary_rates');
    }
};
