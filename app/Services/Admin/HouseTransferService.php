<?php

namespace App\Services\Admin;

use App\Enums\HouseTransferType;
use App\Enums\MembershipRole;
use App\Enums\OwnershipStatus;
use App\Models\HouseOwnership;
use App\Models\HouseUnit;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Auth\UserAccountProvisioningService;
use App\Support\UserEmailRules;
use App\Support\UsernameGenerator;
use App\Traits\HandlesUploads;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HouseTransferService
{
    use HandlesUploads;

    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly UserAccountProvisioningService $provisioning,
        private readonly UsernameGenerator $usernameGenerator,
        private readonly EmailSettingsService $emailSettings,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function transfer(
        HouseUnit $houseUnit,
        User $actor,
        array $data,
        ?UploadedFile $idProof,
        ?UploadedFile $profileImage,
    ): HouseOwnership {
        return DB::transaction(function () use ($houseUnit, $actor, $data, $idProof, $profileImage) {
            $houseUnit = HouseUnit::query()->lockForUpdate()->findOrFail($houseUnit->id);
            $effectiveDate = $data['effective_date'];
            $transferType = HouseTransferType::from($data['transfer_type']);
            $currentOwnership = HouseOwnership::query()
                ->where('house_unit_id', $houseUnit->id)
                ->whereNull('ended_at')
                ->orderByDesc('started_at')
                ->with('mainMember')
                ->first();

            if ($currentOwnership) {
                $formerOwner = $currentOwnership->mainMember;

                if (! $formerOwner || ! $formerOwner->isMainMember()) {
                    throw ValidationException::withMessages([
                        'house' => [__('messages.houses_transfer_invalid_owner')],
                    ]);
                }

                $this->deactivateFormerOwner($formerOwner);

                $currentOwnership->update([
                    'ended_at' => $effectiveDate,
                    'transfer_type' => $transferType->value,
                    'notes' => filled($data['notes'] ?? null) ? trim($data['notes']) : null,
                    'transferred_by_user_id' => $actor->id,
                ]);
            }

            $newOwner = $this->createNewOwner(
                $houseUnit,
                $data,
                $idProof,
                $profileImage,
                $actor,
            );

            $ownership = HouseOwnership::query()->create([
                'house_unit_id' => $houseUnit->id,
                'main_member_user_id' => $newOwner->id,
                'started_at' => $effectiveDate,
                'transfer_type' => $currentOwnership ? $transferType->value : null,
                'notes' => filled($data['notes'] ?? null) ? trim($data['notes']) : null,
                'transferred_by_user_id' => $actor->id,
            ]);

            $houseUnit->update(['current_ownership_id' => $ownership->id]);

            return $ownership->load('mainMember');
        });
    }

    private function deactivateFormerOwner(User $formerOwner): void
    {
        $formerOwner->householdMembers()
            ->get()
            ->each(fn (User $member) => $member->delete());

        $formerOwner->update([
            'ownership_status' => OwnershipStatus::FormerOwner->value,
            'committee_role' => null,
            'role' => User::syncLegacyRole($formerOwner->membership_type, null),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createNewOwner(
        HouseUnit $houseUnit,
        array $data,
        ?UploadedFile $idProof,
        ?UploadedFile $profileImage,
        User $actor,
    ): User {
        $firstName = trim($data['first_name']);
        $middleName = filled($data['middle_name'] ?? null) ? trim($data['middle_name']) : null;
        $lastName = trim($data['last_name']);
        $plainPassword = $this->provisioning->generatePassword();

        $payload = [
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'name' => trim(collect([$firstName, $middleName, $lastName])->filter()->implode(' ')),
            'caste' => filled($data['caste'] ?? null) ? trim($data['caste']) : null,
            'gender' => $data['gender'],
            'house_type' => $houseUnit->house_type->value,
            'house_number' => $houseUnit->house_number,
            'house_unit_id' => $houseUnit->id,
            'ownership_status' => OwnershipStatus::Active->value,
            'mobile_number' => trim($data['mobile_number']),
            'alternate_number' => filled($data['alternate_number'] ?? null) ? trim($data['alternate_number']) : null,
            'email' => UserEmailRules::normalize($data['email'] ?? null),
            'username' => $this->usernameGenerator->generate(
                $firstName,
                $data['gender'],
                $houseUnit->house_type->value,
                $houseUnit->house_number,
            ),
            'password' => $plainPassword,
            'membership_type' => MembershipRole::MainMember->value,
            'committee_role' => null,
            'role' => User::syncLegacyRole(MembershipRole::MainMember->value, null),
        ];

        if ($idProof) {
            $payload['id_proof_path'] = $this->storePublicUpload($idProof, 'users/id-proofs');
        }

        if ($profileImage) {
            $payload['profile_image_path'] = $this->storePublicUpload($profileImage, 'users/profile-images');
        }

        $user = $this->users->create($payload);

        if (filled($user->email) && $this->emailSettings->emailsEnabled()) {
            $this->provisioning->notifyCredentials($user, $plainPassword, $actor);
        }

        return $user;
    }
}
