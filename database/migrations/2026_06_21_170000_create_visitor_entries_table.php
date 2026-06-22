<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('house_unit_id')->constrained('house_units')->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('host_type', 32);
            $table->string('visitor_name');
            $table->string('visitor_contact', 20);
            $table->unsignedSmallInteger('party_size')->default(1);
            $table->unsignedSmallInteger('male_count')->default(0);
            $table->unsignedSmallInteger('female_count')->default(0);
            $table->unsignedSmallInteger('children_count')->default(0);
            $table->string('photo_path')->nullable();
            $table->string('id_proof_path');
            $table->string('vehicle_number', 32)->nullable();
            $table->string('purpose', 64)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('entry_at');
            $table->timestamp('exit_at')->nullable();
            $table->string('status', 16)->default('active');
            $table->foreignId('logged_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'entry_at'], 'visitor_entries_status_entry_idx');
            $table->index(['host_type', 'entry_at'], 'visitor_entries_host_type_entry_idx');
            $table->index('house_unit_id', 'visitor_entries_house_unit_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_entries');
    }
};
