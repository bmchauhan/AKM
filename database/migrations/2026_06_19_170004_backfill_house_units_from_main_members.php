<?php

use App\Enums\MembershipRole;
use App\Enums\OwnershipStatus;
use App\Models\FinanceCollection;
use App\Models\HouseOwnership;
use App\Models\HouseUnit;
use App\Models\MaintenanceMonthlyEntry;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $mainMembers = User::query()
            ->where('membership_type', MembershipRole::MainMember->value)
            ->whereNotNull('house_type')
            ->whereNotNull('house_number')
            ->whereNull('deleted_at')
            ->get();

        foreach ($mainMembers as $user) {
            $houseType = $user->house_type instanceof \BackedEnum
                ? $user->house_type->value
                : (string) $user->house_type;

            $unit = HouseUnit::query()->firstOrCreate(
                [
                    'house_type' => $houseType,
                    'house_number' => trim((string) $user->house_number),
                ],
            );

            DB::table('users')->where('id', $user->id)->update([
                'house_unit_id' => $unit->id,
                'ownership_status' => OwnershipStatus::Active->value,
            ]);

            FinanceCollection::query()
                ->where('main_member_id', $user->id)
                ->update(['house_unit_id' => $unit->id]);

            MaintenanceMonthlyEntry::query()
                ->where('main_member_id', $user->id)
                ->update(['house_unit_id' => $unit->id]);
        }

        HouseUnit::query()->each(function (HouseUnit $unit): void {
            $activeOwner = User::query()
                ->where('house_unit_id', $unit->id)
                ->where('membership_type', MembershipRole::MainMember->value)
                ->where('ownership_status', OwnershipStatus::Active->value)
                ->whereNull('deleted_at')
                ->orderByDesc('id')
                ->first();

            if (! $activeOwner) {
                return;
            }

            $existing = HouseOwnership::query()
                ->where('house_unit_id', $unit->id)
                ->whereNull('ended_at')
                ->first();

            if ($existing) {
                $unit->update(['current_ownership_id' => $existing->id]);

                return;
            }

            $ownership = HouseOwnership::query()->create([
                'house_unit_id' => $unit->id,
                'main_member_user_id' => $activeOwner->id,
                'started_at' => $activeOwner->created_at?->toDateString() ?? now()->toDateString(),
            ]);

            $unit->update(['current_ownership_id' => $ownership->id]);
        });
    }

    public function down(): void
    {
        DB::table('users')->update([
            'house_unit_id' => null,
            'ownership_status' => OwnershipStatus::Active->value,
        ]);

        FinanceCollection::query()->update(['house_unit_id' => null]);
        MaintenanceMonthlyEntry::query()->update(['house_unit_id' => null]);

        HouseOwnership::query()->delete();
        HouseUnit::query()->update(['current_ownership_id' => null]);
        HouseUnit::query()->delete();
    }
};
