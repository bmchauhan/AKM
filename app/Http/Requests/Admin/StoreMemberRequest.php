<?php

namespace App\Http\Requests\Admin;

use App\Enums\Gender;
use App\Enums\HouseType;
use App\Enums\MembershipRole;
use App\Support\MemberHouseSync;
use App\Support\UserEmailRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('members.create') ?? false;
    }

    public function rules(): array
    {
        $user = $this->user();

        return [
            'household_scope' => [
                Rule::requiredIf(fn () => $user?->canChooseHouseholdScope() ?? false),
                Rule::in(['self', 'others']),
            ],
            'membership_type' => [
                'required',
                'string',
                Rule::in([
                    MembershipRole::FamilyMember->value,
                    MembershipRole::RentalMember->value,
                ]),
            ],
            'linked_main_member_id' => [
                Rule::requiredIf(fn () => $this->requiresMainMemberSelection()),
                'integer',
                'exists:users,id',
            ],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'caste' => ['nullable', 'string', 'max:100'],
            'gender' => ['required', Rule::enum(Gender::class)],
            'house_type' => ['required', Rule::enum(HouseType::class)],
            'house_number' => ['required', 'string', 'max:20', 'regex:/^\d+$/'],
            'mobile_number' => ['required', 'string', 'max:20'],
            'alternate_number' => ['nullable', 'string', 'max:20'],
            'email' => UserEmailRules::rules(
                exceptUserId: null,
                membershipType: $this->input('membership_type'),
            ),
            'id_proof' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:1024'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
        ];
    }

    public function messages(): array
    {
        return [
            'membership_type.required' => __('messages.members_type_required'),
            'membership_type.in' => __('messages.members_type_invalid'),
            'linked_main_member_id.required' => __('messages.members_main_member_required'),
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
            'id_proof.max' => __('messages.users_id_proof_size'),
            'profile_image.max' => __('messages.users_profile_image_size'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $user = $this->user();

        $this->merge([
            'email' => UserEmailRules::normalize($this->input('email')),
        ]);

        if ($user?->canChooseHouseholdScope()) {
            if ($this->input('household_scope', 'self') === 'self') {
                $this->merge([
                    'linked_main_member_id' => $user->id,
                ]);
            }

            MemberHouseSync::mergeFromMainMember($this);

            return;
        }

        if ($user?->isMainMember() && ! $user->canManageAnyHousehold()) {
            $this->merge([
                'linked_main_member_id' => $user->id,
            ]);
        }

        MemberHouseSync::mergeFromMainMember($this);
    }

    private function requiresMainMemberSelection(): bool
    {
        $user = $this->user();

        if (! $user) {
            return true;
        }

        if ($user->canChooseHouseholdScope()) {
            return $this->input('household_scope') === 'others';
        }

        return $user->canManageAnyHousehold() || ! $user->isMainMember();
    }
}
