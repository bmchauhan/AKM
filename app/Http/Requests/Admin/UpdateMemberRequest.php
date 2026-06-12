<?php

namespace App\Http\Requests\Admin;

use App\Enums\Gender;
use App\Enums\HouseType;
use App\Enums\MembershipRole;
use App\Models\User;
use App\Services\Admin\AdminMemberService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $memberId = session(AdminMemberService::SESSION_EDITING_MEMBER);
        $member = $memberId ? User::query()->find($memberId) : null;

        return $this->user() && $member
            ? $this->user()->can('members.manage', [$member, 'update'])
            : false;
    }

    public function rules(): array
    {
        $memberId = session(AdminMemberService::SESSION_EDITING_MEMBER);
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
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($memberId)],
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9._-]+$/', Rule::unique('users', 'username')->ignore($memberId)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'id_proof' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:1024'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
        ];
    }

    public function messages(): array
    {
        return (new StoreMemberRequest)->messages();
    }

    protected function prepareForValidation(): void
    {
        $user = $this->user();

        if ($user?->canChooseHouseholdScope()) {
            if ($this->input('household_scope', 'self') === 'self') {
                $this->merge([
                    'linked_main_member_id' => $user->id,
                ]);
            }

            return;
        }

        if ($user?->isMainMember() && ! $user->canManageAnyHousehold()) {
            $this->merge([
                'linked_main_member_id' => $user->id,
            ]);
        }
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
