<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('society_fund_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->date('opening_balance_effective_date');
            $table->text('notes')->nullable();
            $table->foreignId('set_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('society_fund_settings');
    }
};
