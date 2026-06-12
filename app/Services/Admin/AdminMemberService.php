<?php

namespace App\Services\Admin;

use App\Enums\MembershipRole;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Traits\HandlesUploads;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminMemberService
{
    use HandlesUploads;

    public const SESSION_MAIN_MEMBER = 'admin.members.main_member_id';

    public const SESSION_EDITING_MEMBER = 'admin.members.editing_member_id';

    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function listForScreen(User $actor, ?int $mainMemberId = null): array
    {
        $resolvedMainMemberId = $this->resolveListMainMemberId($actor, $mainMemberId);

        if (! $resolvedMainMemberId) {
            return [];
        }

        return $this->users->householdMembersForMainMember($resolvedMainMemberId)
            ->map(fn (User $member) => [
                'id' => $member->id,
                'name' => $member->fullName(),
                'mobile' => $member->mobile_number ?? '—',
                'house' => $member->houseLabel() ?? '—',
                'gender' => $member->gender?->label() ?? '—',
                'membership_type' => $member->membership_type
                    ? MembershipRole::from($member->membership_type)->label().' ('.MembershipRole::from($member->membership_type)->shortForm().')'
                    : '—',
                'profile_image_url' => $member->profileImageUrl(),
                'main_member' => $member->mainMember?->fullName() ?? '—',
            ])
            ->values()
            ->all();
    }

    public function mainMembersForSelect(User $actor): array
    {
        if ($actor->isMainMember() && ! $actor->canManageAnyHousehold()) {
            return [[
                'value' => $actor->id,
                'label' => $this->mainMemberSelectLabel($actor),
                'house_type' => $actor->house_type?->value,
                'house_number' => $actor->house_number,
            ]];
        }

        return $this->users->mainMembersForSelect()
            ->map(fn (User $member) => [
                'value' => $member->id,
                'label' => $this->mainMemberSelectLabel($member),
                'house_type' => $member->house_type?->value,
                'house_number' => $member->house_number,
            ])
            ->all();
    }

    private function mainMemberSelectLabel(User $member): string
    {
        $house = $member->houseLabel();

        if (! $house) {
            return $member->fullName();
        }

        return __('messages.members_main_member_option', [
            'name' => $member->fullName(),
            'house' => $house,
        ]);
    }

    public function defaultMainMemberId(User $actor): ?int
    {
        if ($actor->isMainMember() && ! $actor->canManageAnyHousehold()) {
            return $actor->id;
        }

        return null;
    }

    public function canPickMainMember(User $actor): bool
    {
        return $actor->canManageAnyHousehold() || ! $actor->isMainMember();
    }

    public function canChooseHouseholdScope(User $actor): bool
    {
        return $actor->canChooseHouseholdScope();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function mainMembersForOthersSelect(User $actor): array
    {
        $options = $this->mainMembersForSelect($actor);

        if (! $actor->canChooseHouseholdScope()) {
            return $options;
        }

        return array_values(array_filter(
            $options,
            fn (array $option) => (int) $option['value'] !== $actor->id,
        ));
    }

    public function othersMainMemberId(User $actor, ?int $selectedMainMemberId): ?int
    {
        if (! $actor->canChooseHouseholdScope()) {
            return $selectedMainMemberId;
        }

        if ($selectedMainMemberId === null || (int) $selectedMainMemberId === $actor->id) {
            return null;
        }

        return $selectedMainMemberId;
    }

    public function selectedMainMemberId(User $actor): ?int
    {
        if ($actor->isMainMember() && ! $actor->canManageAnyHousehold()) {
            return $actor->id;
        }

        $storedId = session(self::SESSION_MAIN_MEMBER);

        return $this->resolveListMainMemberId($actor, $storedId ? (int) $storedId : null);
    }

    public function rememberSelectedMainMember(User $actor, int $mainMemberId): void
    {
        if ($actor->isMainMember() && ! $actor->canManageAnyHousehold()) {
            session([self::SESSION_MAIN_MEMBER => $actor->id]);

            return;
        }

        $resolvedId = $this->resolveListMainMemberId($actor, $mainMemberId);

        if (! $resolvedId) {
            throw ValidationException::withMessages([
                'main_member_id' => [__('messages.members_main_member_required')],
            ]);
        }

        session([self::SESSION_MAIN_MEMBER => $resolvedId]);
    }

    public function rememberEditingMember(User $member): void
    {
        session([self::SESSION_EDITING_MEMBER => $member->id]);
    }

    public function editingMember(): ?User
    {
        $memberId = session(self::SESSION_EDITING_MEMBER);

        if (! $memberId) {
            return null;
        }

        return $this->users->findById((int) $memberId);
    }

    public function clearEditingMember(): void
    {
        session()->forget(self::SESSION_EDITING_MEMBER);
    }

    public function membershipTypesForSelect(): array
    {
        return [
            [
                'value' => MembershipRole::FamilyMember->value,
                'label' => __('messages.members_type_family'),
            ],
            [
                'value' => MembershipRole::RentalMember->value,
                'label' => __('messages.members_type_rental'),
            ],
        ];
    }

    public function assertCanManage(User $actor, User $target, string $action = 'update'): void
    {
        Gate::forUser($actor)->authorize('members.manage', [$target, $action]);
    }

    public function create(User $actor, array $data, ?UploadedFile $idProof, ?UploadedFile $profileImage): User
    {
        $mainMember = $this->resolveMainMember($actor, (int) $data['linked_main_member_id']);
        $payload = $this->buildPayload($data, $mainMember);

        if ($idProof) {
            $payload['id_proof_path'] = $this->storePublicUpload($idProof, 'users/id-proofs');
        }

        if ($profileImage) {
            $payload['profile_image_path'] = $this->storePublicUpload($profileImage, 'users/profile-images');
        }

        return $this->users->create($payload);
    }

    public function update(User $actor, User $member, array $data, ?UploadedFile $idProof, ?UploadedFile $profileImage): User
    {
        $this->assertCanManage($actor, $member);
        $mainMember = $this->resolveMainMember($actor, (int) $data['linked_main_member_id'], $member);
        $payload = $this->buildPayload($data, $mainMember, $member);

        if ($idProof) {
            $this->deletePublicUpload($member->id_proof_path);
            $payload['id_proof_path'] = $this->storePublicUpload($idProof, 'users/id-proofs');
        }

        if ($profileImage) {
            $this->deletePublicUpload($member->profile_image_path);
            $payload['profile_image_path'] = $this->storePublicUpload($profileImage, 'users/profile-images');
        }

        if (empty($data['password'])) {
            unset($payload['password']);
        }

        return $this->users->update($member, $payload);
    }

    public function delete(User $member, User $actor): void
    {
        $this->assertCanManage($actor, $member, 'delete');

        if ($member->id === $actor->id) {
            throw ValidationException::withMessages([
                'member' => [__('messages.members_delete_self')],
            ]);
        }

        $this->deletePublicUpload($member->id_proof_path);
        $this->deletePublicUpload($member->profile_image_path);

        $this->users->delete($member);
    }

    public function resolveListMainMemberId(User $actor, ?int $mainMemberId): ?int
    {
        if ($actor->isMainMember() && ! $actor->canManageAnyHousehold()) {
            return $actor->id;
        }

        if (! $mainMemberId) {
            return null;
        }

        $mainMember = $this->users->findById($mainMemberId);

        if (! $mainMember?->isMainMember()) {
            return null;
        }

        return $mainMember->id;
    }

    private function resolveMainMember(User $actor, int $mainMemberId, ?User $existingMember = null): User
    {
        if ($actor->isMainMember() && ! $actor->canManageAnyHousehold()) {
            return $actor;
        }

        $mainMember = $this->users->findById($mainMemberId);

        if (! $mainMember?->isMainMember()) {
            throw ValidationException::withMessages([
                'linked_main_member_id' => [__('messages.members_main_member_required')],
            ]);
        }

        if ($existingMember && (int) $existingMember->linked_main_member_id !== $mainMember->id) {
            throw new AuthorizationException(__('messages.admin_module_forbidden'));
        }

        return $mainMember;
    }

    private function buildPayload(array $data, User $mainMember, ?User $member = null): array
    {
        $firstName = trim($data['first_name']);
        $middleName = filled($data['middle_name'] ?? null) ? trim($data['middle_name']) : null;
        $lastName = trim($data['last_name']);

        $payload = [
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'name' => trim(collect([$firstName, $middleName, $lastName])->filter()->implode(' ')),
            'caste' => filled($data['caste'] ?? null) ? trim($data['caste']) : null,
            'gender' => $data['gender'],
            'house_type' => $data['house_type'] ?? $mainMember->house_type?->value,
            'house_number' => trim($data['house_number'] ?? $mainMember->house_number ?? ''),
            'mobile_number' => trim($data['mobile_number']),
            'alternate_number' => filled($data['alternate_number'] ?? null) ? trim($data['alternate_number']) : null,
            'email' => trim($data['email']),
            'username' => trim($data['username']),
            'membership_type' => $membershipType = $this->resolveMembershipRole($data),
            'committee_role' => null,
            'role' => User::syncLegacyRole($membershipType, null),
            'linked_main_member_id' => $mainMember->id,
        ];

        if (filled($data['password'] ?? null)) {
            $payload['password'] = $data['password'];
        } elseif (! $member) {
            $payload['password'] = Str::password(12);
        }

        return $payload;
    }

    private function resolveMembershipRole(array $data): string
    {
        $type = $data['membership_type'] ?? MembershipRole::FamilyMember->value;

        if (! in_array($type, [
            MembershipRole::FamilyMember->value,
            MembershipRole::RentalMember->value,
        ], true)) {
            throw ValidationException::withMessages([
                'membership_type' => [__('messages.members_type_invalid')],
            ]);
        }

        return $type;
    }
}
