<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_collections', function (Blueprint $table) {
            $table->id();
            $table->string('collection_type', 40);
            $table->foreignId('main_member_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('received_on');
            $table->string('payment_mode', 30)->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['collection_type', 'received_on']);
            $table->index(['main_member_id', 'received_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_collections');
    }
};
