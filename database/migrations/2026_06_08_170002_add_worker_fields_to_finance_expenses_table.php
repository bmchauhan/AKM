<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_expenses', function (Blueprint $table) {
            $table->foreignId('worker_id')->nullable()->after('expense_tag')->constrained('workers')->nullOnDelete();
            $table->decimal('salary_base_amount', 12, 2)->nullable()->after('amount');
            $table->decimal('salary_adjustment', 12, 2)->default(0)->after('salary_base_amount');
            $table->string('salary_adjustment_note')->nullable()->after('salary_adjustment');
        });
    }

    public function down(): void
    {
        Schema::table('finance_expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('worker_id');
            $table->dropColumn(['salary_base_amount', 'salary_adjustment', 'salary_adjustment_note']);
        });
    }
};
