<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('house_ownerships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('house_unit_id')->constrained('house_units')->cascadeOnDelete();
            $table->foreignId('main_member_user_id')->constrained('users');
            $table->date('started_at');
            $table->date('ended_at')->nullable();
            $table->string('transfer_type', 30)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('transferred_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['house_unit_id', 'ended_at'], 'house_ownerships_unit_active_idx');
            $table->index('main_member_user_id', 'house_ownerships_member_idx');
        });

        Schema::table('house_units', function (Blueprint $table) {
            $table->foreign('current_ownership_id', 'house_units_current_ownership_fk')
                ->references('id')
                ->on('house_ownerships')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('house_units', function (Blueprint $table) {
            $table->dropForeign('house_units_current_ownership_fk');
        });

        Schema::dropIfExists('house_ownerships');
    }
};
