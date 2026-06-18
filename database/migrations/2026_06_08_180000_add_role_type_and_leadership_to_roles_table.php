<?php

use App\Enums\RoleType;
use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const LEADERSHIP_SLUGS = [
        'chief_committee_member',
        'vice_chief_committee_member',
    ];

    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('role_type', 30)->default(RoleType::Committee->value)->after('slug');
            $table->boolean('is_leadership')->default(false)->after('role_type');
        });

        DB::table('roles')
            ->where('slug', UserRole::SuperAdmin->value)
            ->update([
                'role_type' => RoleType::SuperAdmin->value,
                'is_leadership' => false,
            ]);

        DB::table('roles')
            ->where('slug', '!=', UserRole::SuperAdmin->value)
            ->update([
                'role_type' => RoleType::Committee->value,
            ]);

        DB::table('roles')
            ->whereIn('slug', self::LEADERSHIP_SLUGS)
            ->update(['is_leadership' => true]);
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['role_type', 'is_leadership']);
        });
    }
};
