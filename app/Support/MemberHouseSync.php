<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

final class MemberHouseSync
{
    public static function mergeFromMainMember(FormRequest $request): void
    {
        $mainMemberId = self::resolveMainMemberId($request);

        if (! $mainMemberId) {
            return;
        }

        $mainMember = User::query()->find($mainMemberId);

        if (! $mainMember?->house_type || ! filled($mainMember->house_number)) {
            return;
        }

        $request->merge([
            'house_type' => $mainMember->house_type->value,
            'house_number' => $mainMember->house_number,
        ]);
    }

    private static function resolveMainMemberId(FormRequest $request): ?int
    {
        $user = $request->user();
        $linkedMainMemberId = $request->input('linked_main_member_id');

        if ($user?->canChooseHouseholdScope()) {
            if ($request->input('household_scope', 'self') === 'self') {
                return $user->id;
            }

            return filled($linkedMainMemberId) ? (int) $linkedMainMemberId : null;
        }

        if ($user?->isMainMember() && ! $user->canManageAnyHousehold()) {
            return $user->id;
        }

        return filled($linkedMainMemberId) ? (int) $linkedMainMemberId : null;
    }
}
