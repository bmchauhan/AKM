<?php

use App\Enums\MembershipRole;
use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const COMMITTEE_SLUGS = [
        'chief_committee_member',
        'vice_chief_committee_member',
        'finance_committee_member',
        'committee_member',
    ];

    private const MEMBERSHIP_SLUGS = [
        'main_member',
        'family_member',
        'rental_member',
    ];

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('membership_type', 50)->nullable()->after('role');
            $table->string('committee_role', 50)->nullable()->after('membership_type');
        });

        $this->migrateExistingRoles();
        $this->removeMembershipRolesFromRolesTable();
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['membership_type', 'committee_role']);
        });
    }

    private function migrateExistingRoles(): void
    {
        DB::table('users')->orderBy('id')->chunkById(200, function ($users) {
            foreach ($users as $user) {
                $role = (string) $user->role;
                $membershipType = null;
                $committeeRole = null;
                $syncedRole = $role;

                if ($role === UserRole::SuperAdmin->value) {
                    $syncedRole = UserRole::SuperAdmin->value;
                } elseif (in_array($role, self::MEMBERSHIP_SLUGS, true)) {
                    $membershipType = $role;
                    $syncedRole = $role;
                } elseif (in_array($role, self::COMMITTEE_SLUGS, true)) {
                    $committeeRole = $role;
                    $hasHouse = filled($user->house_type) && filled($user->house_number);
                    $membershipType = $hasHouse ? MembershipRole::MainMember->value : null;
                    $syncedRole = $role;
                }

                DB::table('users')->where('id', $user->id)->update([
                    'membership_type' => $membershipType,
                    'committee_role' => $committeeRole,
                    'role' => $syncedRole,
                ]);
            }
        });
    }

    private function removeMembershipRolesFromRolesTable(): void
    {
        $membershipRoleIds = DB::table('roles')
            ->whereIn('slug', self::MEMBERSHIP_SLUGS)
            ->pluck('id');

        if ($membershipRoleIds->isNotEmpty()) {
            DB::table('module_role')->whereIn('role_id', $membershipRoleIds)->delete();
            DB::table('roles')->whereIn('id', $membershipRoleIds)->delete();
        }
    }
};
