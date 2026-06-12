<?php

namespace App\Services\Admin;

use App\Enums\MembershipRole;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    /**
     * @return array{
     *     society_stats: list<array{key: string, label: string, value: string|int, hint: ?string}>,
     *     household_stats: list<array{key: string, label: string, value: string|int, hint: ?string}>,
     *     resident_card: ?array{membership_label: string, main_member_name: string, house: string, linked_label: string}
     * }
     */
    public function screenData(User $actor): array
    {
        return [
            'society_stats' => $this->shouldShowSocietyStats($actor)
                ? $this->societyStats($actor)
                : [],
            'household_stats' => $actor->isMainMember()
                ? $this->householdStats($actor)
                : [],
            'resident_card' => $this->residentCard($actor),
        ];
    }

    private function shouldShowSocietyStats(User $actor): bool
    {
        return $actor->isSuperAdmin() || $actor->hasCommitteeRole();
    }

    /**
     * @return list<array{key: string, label: string, value: string|int, hint: ?string}>
     */
    private function societyStats(User $actor): array
    {
        $counts = User::query()
            ->where('role', '!=', UserRole::SuperAdmin->value)
            ->selectRaw('COUNT(*) as total_users')
            ->selectRaw('SUM(CASE WHEN membership_type = ? THEN 1 ELSE 0 END) as main_members', [MembershipRole::MainMember->value])
            ->selectRaw('SUM(CASE WHEN membership_type = ? THEN 1 ELSE 0 END) as family_members', [MembershipRole::FamilyMember->value])
            ->selectRaw('SUM(CASE WHEN membership_type = ? THEN 1 ELSE 0 END) as rental_members', [MembershipRole::RentalMember->value])
            ->selectRaw('SUM(CASE WHEN committee_role IS NOT NULL THEN 1 ELSE 0 END) as committee_members')
            ->first();

        $stats = [
            [
                'key' => 'total_users',
                'label' => __('messages.dashboard_stat_total_users'),
                'value' => (int) ($counts->total_users ?? 0),
                'hint' => __('messages.dashboard_stat_total_users_hint'),
            ],
            [
                'key' => 'main_members',
                'label' => __('messages.dashboard_stat_main_members'),
                'value' => (int) ($counts->main_members ?? 0),
                'hint' => __('messages.dashboard_stat_main_members_hint'),
            ],
            [
                'key' => 'family_members',
                'label' => __('messages.dashboard_stat_family_members'),
                'value' => (int) ($counts->family_members ?? 0),
                'hint' => __('messages.dashboard_stat_family_members_hint'),
            ],
            [
                'key' => 'rental_members',
                'label' => __('messages.dashboard_stat_rental_members'),
                'value' => (int) ($counts->rental_members ?? 0),
                'hint' => __('messages.dashboard_stat_rental_members_hint'),
            ],
        ];

        if ($actor->isSuperAdmin() || $actor->hasCommitteeRole()) {
            $stats[] = [
                'key' => 'committee_members',
                'label' => __('messages.dashboard_stat_committee_members'),
                'value' => (int) ($counts->committee_members ?? 0),
                'hint' => __('messages.dashboard_stat_committee_members_hint'),
            ];
        }

        return $stats;
    }

    /**
     * @return list<array{key: string, label: string, value: string|int, hint: ?string}>
     */
    private function householdStats(User $actor): array
    {
        $household = $this->householdCounts($actor);

        return [
            [
                'key' => 'my_house',
                'label' => __('messages.dashboard_stat_my_house'),
                'value' => $actor->houseLabel() ?? '—',
                'hint' => null,
            ],
            [
                'key' => 'my_family_members',
                'label' => __('messages.dashboard_stat_my_family'),
                'value' => $household['family'],
                'hint' => __('messages.dashboard_stat_my_family_hint'),
            ],
            [
                'key' => 'my_rental_members',
                'label' => __('messages.dashboard_stat_my_rental'),
                'value' => $household['rental'],
                'hint' => __('messages.dashboard_stat_my_rental_hint'),
            ],
            [
                'key' => 'profile_complete',
                'label' => __('messages.dashboard_stat_profile_complete'),
                'value' => $this->profileCompleteness($actor).'%',
                'hint' => __('messages.dashboard_stat_profile_complete_hint'),
            ],
        ];
    }

    /**
     * @return array{family: int, rental: int, total: int}
     */
    private function householdCounts(User $actor): array
    {
        $rows = User::query()
            ->where('linked_main_member_id', $actor->id)
            ->whereIn('membership_type', [
                MembershipRole::FamilyMember->value,
                MembershipRole::RentalMember->value,
            ])
            ->select('membership_type', DB::raw('COUNT(*) as total'))
            ->groupBy('membership_type')
            ->pluck('total', 'membership_type');

        $family = (int) ($rows[MembershipRole::FamilyMember->value] ?? 0);
        $rental = (int) ($rows[MembershipRole::RentalMember->value] ?? 0);

        return [
            'family' => $family,
            'rental' => $rental,
            'total' => $family + $rental,
        ];
    }

    private function profileCompleteness(User $user): int
    {
        $checks = [
            filled($user->first_name),
            filled($user->last_name),
            filled($user->email),
            filled($user->username),
            filled($user->mobile_number),
            $user->gender !== null,
            $user->house_type !== null,
            filled($user->house_number),
            filled($user->profile_image_path),
            filled($user->id_proof_path),
        ];

        $filled = count(array_filter($checks));

        return (int) round(($filled / count($checks)) * 100);
    }

    /**
     * @return array{membership_label: string, main_member_name: string, house: string, linked_label: string}|null
     */
    private function residentCard(User $actor): ?array
    {
        if (! $actor->isFamilyMember() && ! $actor->isRentalMember()) {
            return null;
        }

        if ($actor->isMainMember() || $actor->isSuperAdmin() || $actor->hasCommitteeRole()) {
            return null;
        }

        $mainMember = $actor->relationLoaded('mainMember')
            ? $actor->mainMember
            : $actor->mainMember()->first();

        $membershipLabel = $actor->membership_type
            ? MembershipRole::from($actor->membership_type)->label().' ('.MembershipRole::from($actor->membership_type)->shortForm().')'
            : '—';

        return [
            'membership_label' => $membershipLabel,
            'main_member_name' => $mainMember?->fullName() ?? '—',
            'house' => $actor->houseLabel() ?? $mainMember?->houseLabel() ?? '—',
            'linked_label' => __('messages.dashboard_resident_linked', [
                'name' => $mainMember?->fullName() ?? '—',
            ]),
        ];
    }
}
