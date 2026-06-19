<?php

namespace App\Services\Admin;

use App\Enums\MembershipRole;
use App\Enums\ModulePermissionAction;
use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Auth\UserAccountProvisioningService;
use App\Services\Admin\EmailSettingsService;
use App\Support\CreatedUserResult;
use App\Support\UserEmailRules;
use App\Support\UsernameGenerator;
use App\Traits\HandlesUploads;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class AdminUserService
{
    use HandlesUploads;

    public const SESSION_EDITING_USER = 'admin.users.editing_user_id';

    public const SESSION_ASSIGNING_USER = 'admin.users.assigning_user_id';

    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly CommitteeRoleRegistry $committeeRoles,
        private readonly UserAccountProvisioningService $provisioning,
        private readonly UsernameGenerator $usernameGenerator,
        private readonly EmailSettingsService $emailSettings,
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

    public function rememberAssigningUser(User $user): void
    {
        session([self::SESSION_ASSIGNING_USER => $user->id]);
    }

    public function assigningUser(): ?User
    {
        $userId = session(self::SESSION_ASSIGNING_USER);

        if (! $userId) {
            return null;
        }

        return $this->users->findById((int) $userId);
    }

    public function clearAssigningUser(): void
    {
        session()->forget(self::SESSION_ASSIGNING_USER);
    }

    public function canAssignRoles(User $actor): bool
    {
        return $actor->isSuperAdmin()
            || $actor->canOnAdminModule('users_all', ModulePermissionAction::Update);
    }

    public function canAssignRoleTo(User $actor, User $target): bool
    {
        if (! $this->canAssignRoles($actor)) {
            return false;
        }

        if ($target->isSuperAdmin()) {
            return $actor->isSuperAdmin();
        }

        if (in_array($target->membership_type, [
            MembershipRole::FamilyMember->value,
            MembershipRole::RentalMember->value,
        ], true)) {
            return $actor->canManageMember($target, 'update');
        }

        return $actor->canManageUser($target, 'update');
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
                    'can_view_household' => $user->isMainMember()
                        && ($actor->isSuperAdmin() || $actor->canOnAdminModule('users_all', ModulePermissionAction::Read)),
                    'can_assign_role' => $this->canAssignRoleTo($actor, $user),
                    'assigned_role' => $this->currentRoleAssignmentKey($user),
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
            ->committee()
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

        return $this->committeeRoles->optionsForSelect(true);
    }

    public function assertCanManage(User $actor, User $target, string $action = 'update'): void
    {
        Gate::forUser($actor)->authorize('users.manage', [$target, $action]);
    }

    public function create(User $actor, array $data, ?UploadedFile $idProof, ?UploadedFile $profileImage): CreatedUserResult
    {
        $this->assertAssignableMembership($actor, $data['membership_type']);
        $this->assertAssignableCommittee($actor, $data['membership_type'], $data['committee_role'] ?? null);

        $plainPassword = $this->provisioning->generatePassword();
        $payload = $this->buildPayload($data, null, $plainPassword);

        if ($idProof) {
            $payload['id_proof_path'] = $this->storePublicUpload($idProof, 'users/id-proofs');
        }

        if ($profileImage) {
            $payload['profile_image_path'] = $this->storePublicUpload($profileImage, 'users/profile-images');
        }

        $user = $this->users->create($payload);
        $emailsEnabled = $this->emailSettings->emailsEnabled();
        $credentialsEmailed = $this->provisioning->notifyCredentials($user, $plainPassword, $actor);

        return new CreatedUserResult(
            $user,
            credentialsEmailed: $credentialsEmailed,
            credentialsSkippedDueToDisabled: filled($user->email) && ! $emailsEnabled,
        );
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

        if ($user->isMainMember() && $user->hasFinancePaymentHistory()) {
            throw ValidationException::withMessages([
                'user_finance_history' => [__('messages.users_delete_finance_history')],
            ]);
        }

        $this->users->delete($user);
    }

    private function buildPayload(array $data, ?User $user = null, ?string $plainPassword = null): array
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
            'email' => UserEmailRules::normalize($data['email'] ?? null),
            'username' => $user
                ? $user->username
                : $this->usernameGenerator->generate(
                    $firstName,
                    $data['gender'],
                    $data['house_type'],
                    trim($data['house_number']),
                ),
            'membership_type' => $membershipType,
            'committee_role' => $committeeRole,
            'role' => User::syncLegacyRole($membershipType, $committeeRole),
        ];

        if ($plainPassword !== null) {
            $payload['password'] = $plainPassword;
        } elseif ($user && filled($data['password'] ?? null)) {
            $payload['password'] = $data['password'];
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

        if (! $actor->isSuperAdmin() && ! $this->canAssignRoles($actor)) {
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

        return $this->committeeRoles->committeeSlugs();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function assignableRolesForSelect(User $actor, ?User $target = null): array
    {
        if (! $this->canAssignRoles($actor)) {
            return [];
        }

        $options = [];

        if ($actor->isSuperAdmin()) {
            $options[] = [
                'value' => UserRole::SuperAdmin->value,
                'label' => __('messages.users_role_super_admin'),
            ];
        }

        foreach (MembershipRole::cases() as $membership) {
            $options[] = [
                'value' => $membership->value,
                'label' => __('messages.users_role_group_membership').': '.$membership->label(),
            ];
        }

        foreach ($this->committeeRoles->committeeRoles() as $role) {
            $options[] = [
                'value' => $role->slug,
                'label' => __('messages.users_role_group_committee').': '.$role->name.' ('.$role->short_form.')',
            ];
        }

        return $options;
    }

    /**
     * @return list<array{value: int, label: string}>
     */
    public function usersForRoleAssignmentSelect(): array
    {
        return User::query()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->reject(fn (User $user): bool => $user->isSuperAdmin())
            ->map(fn (User $user): array => [
                'value' => $user->id,
                'label' => trim(($user->houseLabel() ? $user->houseLabel().' — ' : '').$user->fullName().' ('.$user->roleLabel().')'),
            ])
            ->values()
            ->all();
    }

    public function currentRoleAssignmentKey(User $user): string
    {
        if ($user->isSuperAdmin()) {
            return UserRole::SuperAdmin->value;
        }

        if (filled($user->committee_role)) {
            return (string) $user->committee_role;
        }

        return (string) ($user->membership_type ?? MembershipRole::MainMember->value);
    }

    public function isAssignableRoleSlug(string $slug): bool
    {
        if ($slug === UserRole::SuperAdmin->value) {
            return true;
        }

        if (MembershipRole::tryFrom($slug)) {
            return true;
        }

        return $this->committeeRoles->isCommitteeSlug($slug);
    }

    public function assignRole(User $actor, User $target, string $assignedRole): User
    {
        if (! $this->canAssignRoles($actor)) {
            throw ValidationException::withMessages([
                'assigned_role' => [__('messages.users_role_super_admin_forbidden')],
            ]);
        }

        if (! $this->canAssignRoleTo($actor, $target)) {
            throw ValidationException::withMessages([
                'user' => [__('messages.users_role_not_assignable')],
            ]);
        }

        if (! $this->isAssignableRoleSlug($assignedRole)) {
            throw ValidationException::withMessages([
                'assigned_role' => [__('messages.users_role_not_assignable')],
            ]);
        }

        $makingSuperAdmin = $assignedRole === UserRole::SuperAdmin->value;

        if ($makingSuperAdmin && ! $actor->isSuperAdmin()) {
            throw ValidationException::withMessages([
                'assigned_role' => [__('messages.users_role_super_admin_forbidden')],
            ]);
        }

        $this->assertSuperAdminChangeAllowed($actor, $target, $makingSuperAdmin);

        $membershipType = $target->membership_type ?? MembershipRole::MainMember->value;
        $committeeRole = null;

        if ($makingSuperAdmin) {
            $committeeRole = null;
        } elseif (MembershipRole::tryFrom($assignedRole)) {
            $membershipType = $assignedRole;
            $committeeRole = null;
        } elseif ($this->committeeRoles->isCommitteeSlug($assignedRole)) {
            if ($membershipType === MembershipRole::RentalMember->value) {
                throw ValidationException::withMessages([
                    'assigned_role' => [__('messages.users_committee_rental_forbidden')],
                ]);
            }

            $committeeRole = $assignedRole;
        }

        if (! $makingSuperAdmin) {
            if (MembershipRole::tryFrom($assignedRole) && $assignedRole !== MembershipRole::FamilyMember->value) {
                $this->assertAssignableMembership($actor, $membershipType);
            }

            $this->assertAssignableCommittee($actor, $membershipType, $committeeRole);
        }

        return $this->users->update($target, [
            'membership_type' => $membershipType,
            'committee_role' => $committeeRole,
            'role' => User::syncLegacyRole($membershipType, $committeeRole, $makingSuperAdmin),
        ]);
    }

    private function assertSuperAdminChangeAllowed(User $actor, User $target, bool $makingSuperAdmin): void
    {
        if ($makingSuperAdmin) {
            return;
        }

        if (! $target->isSuperAdmin()) {
            return;
        }

        $remaining = User::query()
            ->where('role', UserRole::SuperAdmin->value)
            ->where('id', '!=', $target->id)
            ->count();

        if ($remaining < 1) {
            throw ValidationException::withMessages([
                'assigned_role' => [__('messages.users_role_last_super_admin')],
            ]);
        }
    }
}
