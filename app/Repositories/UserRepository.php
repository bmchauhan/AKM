<?php

namespace App\Repositories;

use App\Enums\MembershipRole;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Collection;

class UserRepository implements UserRepositoryInterface
{
    public function allForAdmin(): Collection
    {
        return User::query()
            ->with('roleRecord')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    public function findById(int $id): ?User
    {
        return User::query()->with('roleRecord')->find($id);
    }

    public function findByEmailOrUsername(string $login): ?User
    {
        return User::query()
            ->where('email', $login)
            ->orWhere('username', $login)
            ->first();
    }

    public function create(array $data): User
    {
        return User::query()->create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user->fresh(['roleRecord']);
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    public function emailExists(string $email, ?int $exceptUserId = null): bool
    {
        return User::query()
            ->when($exceptUserId, fn ($query) => $query->where('id', '!=', $exceptUserId))
            ->where('email', $email)
            ->exists();
    }

    public function usernameExists(string $username, ?int $exceptUserId = null): bool
    {
        return User::query()
            ->when($exceptUserId, fn ($query) => $query->where('id', '!=', $exceptUserId))
            ->where('username', $username)
            ->exists();
    }

    public function householdMembersForMainMember(int $mainMemberId): Collection
    {
        return User::query()
            ->with('mainMember')
            ->whereIn('role', [
                MembershipRole::FamilyMember->value,
                MembershipRole::RentalMember->value,
            ])
            ->where('linked_main_member_id', $mainMemberId)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    public function mainMembersForSelect(): Collection
    {
        return User::query()
            ->where('role', MembershipRole::MainMember->value)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'house_type', 'house_number']);
    }
}
