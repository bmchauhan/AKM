<?php

namespace App\Services\Admin;

use App\Enums\HouseType;
use App\Enums\MembershipRole;
use App\Enums\OwnershipStatus;
use App\Models\HouseUnit;
use App\Models\User;
use Illuminate\Support\Collection;

class HouseUnitSyncService
{
    public function syncForMainMember(User $user): ?HouseUnit
    {
        if (! $user->isMainMember() || ! $user->house_type || ! $user->house_number) {
            return null;
        }

        $unit = HouseUnit::query()->firstOrCreate([
            'house_type' => $user->house_type->value,
            'house_number' => trim((string) $user->house_number),
        ]);

        if ($user->house_unit_id !== $unit->id) {
            $user->update([
                'house_unit_id' => $unit->id,
                'ownership_status' => OwnershipStatus::Active->value,
            ]);
        }

        if ($unit->current_ownership_id === null) {
            $ownership = $unit->ownerships()->create([
                'main_member_user_id' => $user->id,
                'started_at' => now()->toDateString(),
            ]);

            $unit->update(['current_ownership_id' => $ownership->id]);
        }

        return $unit->fresh();
    }

    /**
     * @return Collection<int, HouseType>
     */
    public function houseTypesForSelect(): Collection
    {
        return collect(HouseType::cases());
    }
}
