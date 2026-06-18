<?php

namespace App\Services;

use App\Enums\RoleType;
use App\Enums\UsefulDirectorySource;
use App\Enums\UserRole;
use App\Models\UsefulDirectoryContact;
use App\Models\UsefulDirectoryRole;
use App\Models\User;

class UsefulDirectoryContactSyncService
{
    public function syncCommitteeMembers(): int
    {
        $committeeRole = UsefulDirectoryRole::query()
            ->where('is_active', true)
            ->where('supports_committee_link', true)
            ->orderBy('sort_order')
            ->first();

        if (! $committeeRole) {
            return 0;
        }

        $users = User::query()
            ->whereNotNull('committee_role')
            ->where('role', '!=', UserRole::SuperAdmin->value)
            ->whereHas('committeeRoleRecord', fn ($query) => $query->where('role_type', RoleType::Committee->value))
            ->with('committeeRoleRecord')
            ->get()
            ->sortBy([
                fn (User $user) => $user->committeeRoleRecord?->is_leadership ? 0 : 1,
                fn (User $user) => $user->committeeRoleRecord?->name ?? $user->committee_role,
                fn (User $user) => $user->fullName(),
            ])
            ->values();

        $created = 0;
        $sortOrder = (int) UsefulDirectoryContact::query()
            ->where('directory_role_id', $committeeRole->id)
            ->max('sort_order');

        foreach ($users as $user) {
            if (UsefulDirectoryContact::query()->where('user_id', $user->id)->exists()) {
                continue;
            }

            UsefulDirectoryContact::query()->create([
                'directory_role_id' => $committeeRole->id,
                'source' => UsefulDirectorySource::CommitteeMember,
                'user_id' => $user->id,
                'title' => $user->committeeRoleRecord?->name ?? __('messages.committee_member_generic'),
                'sort_order' => ++$sortOrder,
                'is_active' => true,
            ]);

            $created++;
        }

        return $created;
    }
}
