<?php

namespace App\Services\Frontend;

use App\Enums\Gender;
use App\Enums\RoleType;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class FrontendCommitteeService
{
    /**
     * @return list<array{
     *     name: string,
     *     position: string,
     *     image: string,
     *     has_photo: bool,
     *     is_chief: bool,
     *     is_leadership: bool,
     *     bio: ?string
     * }>
     */
    public function publicMembers(): array
    {
        return $this->publicMemberCollection()->all();
    }

    /**
     * @return Collection<int, array{name: string, position: string, image: string, has_photo: bool, is_chief: bool, is_leadership: bool, bio: ?string}>
     */
    public function publicMemberCollection(): Collection
    {
        return User::query()
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
            ->values()
            ->map(fn (User $user) => $this->toPublicMember($user));
    }

    /**
     * @return array{name: string, position: string, image: string, has_photo: bool, is_chief: bool, is_leadership: bool, bio: ?string}
     */
    private function toPublicMember(User $user): array
    {
        $role = $user->committeeRoleRecord;
        $isLeadership = (bool) ($role?->is_leadership ?? false);

        $hasPhoto = filled($user->profile_image_path)
            && Storage::disk('public')->exists($user->profile_image_path);

        return [
            'name' => $user->fullName(),
            'position' => $role?->name ?? __('messages.committee_member_generic'),
            'image' => $hasPhoto ? (string) $user->profileImageUrl() : $this->defaultAvatarUrl($user->gender),
            'has_photo' => $hasPhoto,
            'is_chief' => $user->isChiefCommitteeMember(),
            'is_leadership' => $isLeadership,
            'bio' => $isLeadership ? $role?->description : null,
        ];
    }

    private function defaultAvatarUrl(?Gender $gender): string
    {
        $file = match ($gender) {
            Gender::Female => 'default-female.svg',
            Gender::Male => 'default-male.svg',
            default => 'default-other.svg',
        };

        return asset('Assets/avatars/'.$file);
    }
}
