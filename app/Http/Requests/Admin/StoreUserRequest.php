<?php

namespace App\Http\Requests\Admin;

use App\Enums\Gender;
use App\Enums\HouseType;
use App\Enums\MembershipRole;
use App\Services\Admin\AdminUserService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.create') ?? false;
    }

    public function rules(): array
    {
        $actor = $this->user();
        $allowedMembership = $actor
            ? app(AdminUserService::class)->allowedMembershipTypesForUserForm($actor)
            : [];
        $allowedCommittee = $actor
            ? app(AdminUserService::class)->allowedCommitteeRolesForUserForm($actor)
            : [];

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'caste' => ['nullable', 'string', 'max:100'],
            'gender' => ['required', Rule::enum(Gender::class)],
            'house_type' => ['required', Rule::enum(HouseType::class)],
            'house_number' => ['required', 'string', 'max:20', 'regex:/^\d+$/'],
            'mobile_number' => ['required', 'string', 'max:20'],
            'alternate_number' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9._-]+$/', 'unique:users,username'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'membership_type' => [
                'required',
                'string',
                Rule::in($allowedMembership),
                Rule::notIn([MembershipRole::FamilyMember->value]),
            ],
            'committee_role' => [
                'nullable',
                'string',
                Rule::when(
                    filled($this->input('committee_role')),
                    [Rule::in($allowedCommittee)],
                ),
                Rule::prohibitedIf(fn () => $this->input('membership_type') === MembershipRole::RentalMember->value),
            ],
            'id_proof' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:1024'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => __('messages.users_first_name_required'),
            'last_name.required' => __('messages.users_last_name_required'),
            'gender.required' => __('messages.users_gender_required'),
            'house_type.required' => __('messages.users_house_type_required'),
            'house_number.required' => __('messages.users_house_number_required'),
            'house_number.regex' => __('messages.users_house_number_numeric'),
            'mobile_number.required' => __('messages.users_mobile_required'),
            'email.required' => __('messages.users_email_required'),
            'username.required' => __('messages.users_username_required'),
            'username.unique' => __('messages.users_username_exists'),
            'username.regex' => __('messages.users_username_format'),
            'email.unique' => __('messages.users_email_exists'),
            'membership_type.required' => __('messages.users_membership_required'),
            'id_proof.max' => __('messages.users_id_proof_size'),
            'profile_image.max' => __('messages.users_profile_image_size'),
        ];
    }
}
