<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            $table->string('worker_type', 40);
            $table->string('name');
            $table->string('mobile_number', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('profile_image_path')->nullable();
            $table->date('joined_on')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['worker_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workers');
    }
};
