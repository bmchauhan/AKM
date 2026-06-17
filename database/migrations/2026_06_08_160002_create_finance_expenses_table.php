<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_tag', 50);
            $table->decimal('amount', 12, 2);
            $table->date('paid_on');
            $table->string('payee_name')->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['expense_tag', 'paid_on']);
            $table->index('paid_on');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_expenses');
    }
};
