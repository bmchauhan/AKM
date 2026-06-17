<?php

namespace App\Services\Admin;

use App\Enums\CommitteeRole;
use App\Enums\MembershipRole;
use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Traits\HandlesUploads;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminUserService
{
    use HandlesUploads;

    public const SESSION_EDITING_USER = 'admin.users.editing_user_id';

    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function rememberEditingUser(User $user): void
    {
        session([self::SESSION_EDITING_USER => $user->id]);
    }

    public function editingUser(): ?User
    {
        $userId = session(self::SESSION_EDITING_USER);

        if (! $userId) {
            return null;
        }

        return $this->users->findById((int) $userId);
    }

    public function clearEditingUser(): void
    {
        session()->forget(self::SESSION_EDITING_USER);
    }

    /**
     * @param  array{house_type?: string, house_number?: string, name?: string, role?: string}  $filters
     * @return array{users: \Illuminate\Pagination\LengthAwarePaginator, filters: array<string, string>, roleOptions: list<array{value: string, label: string}>}
     */
    public function listForScreen(User $actor, array $filters = [], int $perPage = 25): array
    {
        $normalizedFilters = [
            'house_type' => trim((string) ($filters['house_type'] ?? '')),
            'house_number' => trim((string) ($filters['house_number'] ?? '')),
            'name' => trim((string) ($filters['name'] ?? '')),
            'role' => trim((string) ($filters['role'] ?? '')),
        ];

        $queryFilters = $normalizedFilters;

        if (! $actor->isSuperAdmin()) {
            $queryFilters['exclude_super_admin'] = true;
        }

        $users = $this->users->paginatedForAdmin($queryFilters, $perPage)
            ->through(function (User $user) use ($actor) {
                $isHouseholdMember = in_array($user->membership_type, [
                    MembershipRole::FamilyMember->value,
                    MembershipRole::RentalMember->value,
                ], true);

                return [
                    'id' => $user->id,
                    'name' => $user->fullName(),
                    'mobile' => $user->mobile_number ?? '—',
                    'house' => $user->houseLabel() ?? '—',
                    'role' => $user->roleLabel(),
                    'gender' => $user->gender?->label() ?? '—',
                    'profile_image_url' => $user->profileImageUrl(),
                    'is_super_admin' => $user->isSuperAdmin(),
                    'is_main_member' => $user->isMainMember(),
                    'is_household_member' => $isHouseholdMember,
                    'can_edit' => $isHouseholdMember
                        ? $actor->canManageMember($user, 'update')
                        : $actor->canManageUser($user, 'update'),
                    'household_count' => (int) ($user->household_members_count ?? 0),
                ];
            });

        return [
            'users' => $users,
            'filters' => $normalizedFilters,
            'roleOptions' => $this->rolesForFilter($actor),
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function rolesForFilter(User $actor): array
    {
        $options = collect(MembershipRole::cases())
            ->map(fn (MembershipRole $type) => [
                'value' => $type->value,
                'label' => $type->label().' ('.$type->shortForm().')',
            ]);

        $committeeOptions = Role::query()
            ->whereIn('slug', array_map(fn (CommitteeRole $role) => $role->value, CommitteeRole::cases()))
            ->orderBy('name')
            ->get(['slug', 'name', 'short_form'])
            ->map(fn (Role $role) => [
                'value' => $role->slug,
                'label' => $role->name.' ('.$role->short_form.')',
            ]);

        if ($actor->isSuperAdmin()) {
            $superAdmin = Role::query()
                ->where('slug', UserRole::SuperAdmin->value)
                ->first(['slug', 'name', 'short_form']);

            if ($superAdmin) {
                $committeeOptions->prepend([
                    'value' => $superAdmin->slug,
                    'label' => $superAdmin->name.' ('.$superAdmin->short_form.')',
                ]);
            }
        }

        return $options
            ->concat($committeeOptions)
            ->sortBy('label')
            ->values()
            ->all();
    }

    /**
     * @return array{main_member: array<string, mixed>, members: list<array<string, mixed>>}
     */
    public function householdMembersForScreen(User $mainMember): array
    {
        if (! $mainMember->isMainMember()) {
            throw ValidationException::withMessages([
                'user' => [__('messages.users_household_not_main_member')],
            ]);
        }

        $members = $this->users->householdMembersForMainMember($mainMember->id)
            ->map(fn (User $member) => [
                'id' => $member->id,
                'name' => $member->fullName(),
                'mobile' => $member->mobile_number ?? '—',
                'house' => $member->houseLabel() ?? '—',
                'role' => $member->roleLabel(),
                'gender' => $member->gender?->label() ?? '—',
                'profile_image_url' => $member->profileImageUrl(),
            ])
            ->values()
            ->all();

        return [
            'main_member' => [
                'id' => $mainMember->id,
                'name' => $mainMember->fullName(),
                'house' => $mainMember->houseLabel() ?? '—',
                'role' => $mainMember->roleLabel(),
                'mobile' => $mainMember->mobile_number ?? '—',
            ],
            'members' => $members,
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function membershipTypesForSelect(User $actor): array
    {
        return collect($this->allowedMembershipTypesForUserForm($actor))
            ->map(fn (string $slug) => MembershipRole::from($slug))
            ->map(fn (MembershipRole $type) => [
                'value' => $type->value,
                'label' => $type->label().' ('.$type->shortForm().')',
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function committeeRolesForSelect(User $actor): array
    {
        if (! $actor->isSuperAdmin()) {
            return [];
        }

        $options = [['value' => '', 'label' => __('messages.users_committee_none')]];

        foreach ($this->allowedCommitteeRolesForUserForm($actor) as $slug) {
            $role = Role::query()->where('slug', $slug)->first(['slug', 'name', 'short_form']);

            $options[] = [
                'value' => $slug,
                'label' => $role
                    ? $role->name.' ('.$role->short_form.')'
                    : CommitteeRole::from($slug)->label().' ('.CommitteeRole::from($slug)->shortForm().')',
            ];
        }

        return $options;
    }

    public function assertCanManage(User $actor, User $target, string $action = 'update'): void
    {
        Gate::forUser($actor)->authorize('users.manage', [$target, $action]);
    }

    public function create(User $actor, array $data, ?UploadedFile $idProof, ?UploadedFile $profileImage): User
    {
        $this->assertAssignableMembership($actor, $data['membership_type']);
        $this->assertAssignableCommittee($actor, $data['membership_type'], $data['committee_role'] ?? null);

        $payload = $this->buildPayload($data);

        if ($idProof) {
            $payload['id_proof_path'] = $this->storePublicUpload($idProof, 'users/id-proofs');
        }

        if ($profileImage) {
            $payload['profile_image_path'] = $this->storePublicUpload($profileImage, 'users/profile-images');
        }

        return $this->users->create($payload);
    }

    public function update(User $actor, User $user, array $data, ?UploadedFile $idProof, ?UploadedFile $profileImage): User
    {
        $this->assertCanManage($actor, $user);
        $this->assertAssignableMembership($actor, $data['membership_type']);

        if (! $actor->isSuperAdmin()) {
            $data['committee_role'] = $user->committee_role;
        }

        $this->assertAssignableCommittee($actor, $data['membership_type'], $data['committee_role'] ?? null);

        $payload = $this->buildPayload($data, $user);

        if ($idProof) {
            $this->deletePublicUpload($user->id_proof_path);
            $payload['id_proof_path'] = $this->storePublicUpload($idProof, 'users/id-proofs');
        }

        if ($profileImage) {
            $this->deletePublicUpload($user->profile_image_path);
            $payload['profile_image_path'] = $this->storePublicUpload($profileImage, 'users/profile-images');
        }

        if (empty($data['password'])) {
            unset($payload['password']);
        }

        return $this->users->update($user, $payload);
    }

    public function delete(User $user, User $actor): void
    {
        $this->assertCanManage($actor, $user, 'delete');

        if ($user->id === $actor->id) {
            throw ValidationException::withMessages([
                'user' => [__('messages.users_delete_self')],
            ]);
        }

        if ($user->isSuperAdmin()) {
            throw ValidationException::withMessages([
                'user' => [__('messages.users_delete_super_admin')],
            ]);
        }

        $this->deletePublicUpload($user->id_proof_path);
        $this->deletePublicUpload($user->profile_image_path);

        $this->users->delete($user);
    }

    private function buildPayload(array $data, ?User $user = null): array
    {
        $firstName = trim($data['first_name']);
        $middleName = filled($data['middle_name'] ?? null) ? trim($data['middle_name']) : null;
        $lastName = trim($data['last_name']);
        $membershipType = $data['membership_type'];
        $committeeRole = filled($data['committee_role'] ?? null) ? $data['committee_role'] : null;

        if ($membershipType === MembershipRole::RentalMember->value) {
            $committeeRole = null;
        }

        $payload = [
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'name' => trim(collect([$firstName, $middleName, $lastName])->filter()->implode(' ')),
            'caste' => filled($data['caste'] ?? null) ? trim($data['caste']) : null,
            'gender' => $data['gender'],
            'house_type' => $data['house_type'],
            'house_number' => trim($data['house_number']),
            'mobile_number' => trim($data['mobile_number']),
            'alternate_number' => filled($data['alternate_number'] ?? null) ? trim($data['alternate_number']) : null,
            'email' => trim($data['email']),
            'username' => trim($data['username']),
            'membership_type' => $membershipType,
            'committee_role' => $committeeRole,
            'role' => User::syncLegacyRole($membershipType, $committeeRole),
        ];

        if (filled($data['password'] ?? null)) {
            $payload['password'] = $data['password'];
        } elseif (! $user) {
            $payload['password'] = Str::password(12);
        }

        return $payload;
    }

    private function assertAssignableMembership(User $actor, string $membershipType): void
    {
        if ($membershipType === MembershipRole::FamilyMember->value) {
            throw ValidationException::withMessages([
                'membership_type' => [__('messages.users_role_family_member_forbidden')],
            ]);
        }

        if (! in_array($membershipType, $this->allowedMembershipTypesForUserForm($actor), true)) {
            throw ValidationException::withMessages([
                'membership_type' => [__('messages.users_membership_not_allowed')],
            ]);
        }
    }

    private function assertAssignableCommittee(User $actor, string $membershipType, ?string $committeeRole): void
    {
        if ($membershipType === MembershipRole::RentalMember->value && filled($committeeRole)) {
            throw ValidationException::withMessages([
                'committee_role' => [__('messages.users_committee_rental_forbidden')],
            ]);
        }

        if (! filled($committeeRole)) {
            return;
        }

        if (! $actor->isSuperAdmin()) {
            throw ValidationException::withMessages([
                'committee_role' => [__('messages.users_committee_not_allowed')],
            ]);
        }

        if (! in_array($committeeRole, $this->allowedCommitteeRolesForUserForm($actor), true)) {
            throw ValidationException::withMessages([
                'committee_role' => [__('messages.users_committee_not_allowed')],
            ]);
        }
    }

    /**
     * @return list<string>
     */
    public function allowedMembershipTypesForUserForm(User $actor): array
    {
        if ($actor->isSuperAdmin() || $actor->hasCommitteeLeadership()) {
            return [
                MembershipRole::MainMember->value,
                MembershipRole::RentalMember->value,
            ];
        }

        return [
            MembershipRole::MainMember->value,
            MembershipRole::RentalMember->value,
        ];
    }

    /**
     * @return list<string>
     */
    public function allowedCommitteeRolesForUserForm(User $actor): array
    {
        if (! $actor->isSuperAdmin()) {
            return [];
        }

        return array_map(
            fn (CommitteeRole $role) => $role->value,
            CommitteeRole::cases(),
        );
    }
}
