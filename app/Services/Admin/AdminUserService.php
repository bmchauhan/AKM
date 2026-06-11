<?php

namespace App\Services\Admin;

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

    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function listForScreen(): array
    {
        return $this->users->allForAdmin()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->fullName(),
                'mobile' => $user->mobile_number ?? '—',
                'house' => $user->houseLabel() ?? '—',
                'role' => $user->roleLabel(),
                'gender' => $user->gender?->label() ?? '—',
                'profile_image_url' => $user->profileImageUrl(),
                'is_super_admin' => $user->isSuperAdmin(),
            ])
            ->values()
            ->all();
    }

    public function rolesForSelect(User $actor): array
    {
        $allowedSlugs = $this->allowedRoleSlugsForUserForm($actor);

        return Role::query()
            ->orderBy('name')
            ->whereIn('slug', $allowedSlugs)
            ->get(['slug', 'name', 'short_form'])
            ->map(fn (Role $role) => [
                'value' => $role->slug,
                'label' => $role->name.' ('.$role->short_form.')',
            ])
            ->all();
    }

    public function assertCanManage(User $actor, User $target, string $action = 'update'): void
    {
        Gate::forUser($actor)->authorize('users.manage', [$target, $action]);
    }

    public function create(User $actor, array $data, ?UploadedFile $idProof, ?UploadedFile $profileImage): User
    {
        $this->assertAssignableRole($actor, $data['role']);

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
        $this->assertAssignableRole($actor, $data['role']);

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
            'role' => $data['role'],
        ];

        if (filled($data['password'] ?? null)) {
            $payload['password'] = $data['password'];
        } elseif (! $user) {
            $payload['password'] = Str::password(12);
        }

        return $payload;
    }

    private function assertAssignableRole(User $actor, string $roleSlug): void
    {
        if ($roleSlug === UserRole::SuperAdmin->value && ! $actor->isSuperAdmin()) {
            throw ValidationException::withMessages([
                'role' => [__('messages.users_role_super_admin_forbidden')],
            ]);
        }

        if ($roleSlug === MembershipRole::FamilyMember->value) {
            throw ValidationException::withMessages([
                'role' => [__('messages.users_role_family_member_forbidden')],
            ]);
        }

        if (! in_array($roleSlug, $this->allowedRoleSlugsForUserForm($actor), true)) {
            throw ValidationException::withMessages([
                'role' => [__('messages.users_role_not_allowed')],
            ]);
        }
    }

    /**
     * @return list<string>
     */
    private function allowedRoleSlugsForUserForm(User $actor): array
    {
        if ($actor->isSuperAdmin()) {
            return Role::query()
                ->where('slug', '!=', MembershipRole::FamilyMember->value)
                ->orderBy('name')
                ->pluck('slug')
                ->all();
        }

        if ($actor->hasCommitteeLeadership()) {
            return [
                MembershipRole::MainMember->value,
                MembershipRole::RentalMember->value,
            ];
        }

        return Role::query()
            ->whereNotIn('slug', [
                UserRole::SuperAdmin->value,
                MembershipRole::FamilyMember->value,
            ])
            ->orderBy('name')
            ->pluck('slug')
            ->all();
    }
}
