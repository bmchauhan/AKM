<?php

namespace App\Repositories;

use App\Enums\MembershipRole;
use App\Enums\OwnershipStatus;
use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserRepository implements UserRepositoryInterface
{
    public function paginatedForAdmin(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->adminListQuery($filters)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array{house_type?: string, house_number?: string, name?: string, role?: string, exclude_super_admin?: bool}  $filters
     */
    private function adminListQuery(array $filters): Builder
    {
        $name = trim((string) ($filters['name'] ?? ''));

        return User::query()
            ->with('roleRecord')
            ->withCount([
                'householdMembers as household_members_count',
            ])
            ->where(function ($query) {
                $query->whereNull('ownership_status')
                    ->orWhere('ownership_status', OwnershipStatus::Active->value);
            })
            ->when(! empty($filters['exclude_super_admin']), function ($query) {
                $query->where('role', '!=', UserRole::SuperAdmin->value);
            })
            ->when(filled($filters['role'] ?? null), function ($query) use ($filters) {
                $slug = $filters['role'];

                $query->where(function ($inner) use ($slug) {
                    $inner->where('membership_type', $slug)
                        ->orWhere('committee_role', $slug)
                        ->orWhere('role', $slug);
                });
            })
            ->when(filled($filters['house_type'] ?? null), function ($query) use ($filters) {
                $query->where('house_type', $filters['house_type']);
            })
            ->when(filled($filters['house_number'] ?? null), function ($query) use ($filters) {
                $query->where('house_number', trim((string) $filters['house_number']));
            })
            ->when($name !== '', function ($query) use ($name) {
                $term = '%'.$name.'%';

                $query->where(function ($inner) use ($term) {
                    $inner->where('first_name', 'like', $term)
                        ->orWhere('middle_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('name', 'like', $term);
                });
            })
            ->orderBy('first_name')
            ->orderBy('last_name');
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
        DB::transaction(function () use ($user): void {
            $user->delete();
        });
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
            ->whereIn('membership_type', [
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
            ->where('membership_type', MembershipRole::MainMember->value)
            ->where(function ($query) {
                $query->whereNull('ownership_status')
                    ->orWhere('ownership_status', OwnershipStatus::Active->value);
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'house_type', 'house_number']);
    }
}
