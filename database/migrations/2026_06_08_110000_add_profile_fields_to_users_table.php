<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->nullable()->after('middle_name');
            $table->string('caste')->nullable()->after('last_name');
            $table->string('gender', 20)->nullable()->after('caste');
            $table->string('house_type', 1)->nullable()->after('gender');
            $table->string('house_number', 20)->nullable()->after('house_type');
            $table->string('mobile_number', 20)->nullable()->after('house_number');
            $table->string('alternate_number', 20)->nullable()->after('mobile_number');
            $table->string('id_proof_path')->nullable()->after('alternate_number');
            $table->string('profile_image_path')->nullable()->after('id_proof_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'middle_name',
                'last_name',
                'caste',
                'gender',
                'house_type',
                'house_number',
                'mobile_number',
                'alternate_number',
                'id_proof_path',
                'profile_image_path',
            ]);
        });
    }
};
