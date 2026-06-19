<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('house_units', function (Blueprint $table) {
            $table->id();
            $table->string('house_type', 5);
            $table->string('house_number', 20);
            $table->unsignedBigInteger('current_ownership_id')->nullable();
            $table->timestamps();

            $table->unique(['house_type', 'house_number'], 'house_units_type_number_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('house_units');
    }
};
