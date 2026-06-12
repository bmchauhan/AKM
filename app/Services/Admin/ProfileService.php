<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Traits\HandlesUploads;
use Illuminate\Http\UploadedFile;

class ProfileService
{
    use HandlesUploads;

    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function update(User $user, array $data, ?UploadedFile $idProof, ?UploadedFile $profileImage): User
    {
        $firstName = trim($data['first_name']);
        $middleName = filled($data['middle_name'] ?? null) ? trim($data['middle_name']) : null;
        $lastName = trim($data['last_name']);

        $payload = [
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'name' => trim(collect([$firstName, $middleName, $lastName])->filter()->implode(' ')),
            'email' => trim($data['email']),
            'mobile_number' => trim($data['mobile_number']),
            'alternate_number' => filled($data['alternate_number'] ?? null) ? trim($data['alternate_number']) : null,
        ];

        if ($idProof) {
            $this->deletePublicUpload($user->id_proof_path);
            $payload['id_proof_path'] = $this->storePublicUpload($idProof, 'users/id-proofs');
        }

        if ($profileImage) {
            $this->deletePublicUpload($user->profile_image_path);
            $payload['profile_image_path'] = $this->storePublicUpload($profileImage, 'users/profile-images');
        }

        return $this->users->update($user, $payload);
    }
}
