<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('useful_directory_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('category', 40);
            $table->string('source', 40)->default('manual');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('contact_name')->nullable();
            $table->string('phone_primary', 20)->nullable();
            $table->string('phone_secondary', 20)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category', 'is_active', 'sort_order']);
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('useful_directory_contacts');
    }
};
