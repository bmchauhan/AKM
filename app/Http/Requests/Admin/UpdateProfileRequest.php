<?php

namespace App\Http\Requests\Admin;

use App\Support\UserEmailRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $user = $this->user();

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => UserEmailRules::rules(
                exceptUserId: $user?->id,
                membershipType: $user?->membership_type,
                committeeRole: $user?->committee_role,
                isSuperAdmin: $user?->isSuperAdmin() ?? false,
            ),
            'mobile_number' => ['required', 'string', 'max:20'],
            'alternate_number' => ['nullable', 'string', 'max:20'],
            'id_proof' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:1024'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => UserEmailRules::normalize($this->input('email')),
        ]);
    }

    public function messages(): array
    {
        return [
            'first_name.required' => __('messages.users_first_name_required'),
            'last_name.required' => __('messages.users_last_name_required'),
            'mobile_number.required' => __('messages.users_mobile_required'),
            'email.required' => __('messages.users_email_required'),
            'email.unique' => __('messages.users_email_exists'),
            'id_proof.max' => __('messages.users_id_proof_size'),
            'profile_image.max' => __('messages.users_profile_image_size'),
        ];
    }
}
