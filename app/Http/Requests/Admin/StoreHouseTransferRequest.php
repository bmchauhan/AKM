<?php

namespace App\Http\Requests\Admin;

use App\Enums\Gender;
use App\Enums\HouseTransferType;
use App\Enums\MembershipRole;
use App\Support\UserEmailRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHouseTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('houses_transfer.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'effective_date' => ['required', 'date'],
            'transfer_type' => ['required', Rule::enum(HouseTransferType::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'caste' => ['nullable', 'string', 'max:100'],
            'gender' => ['required', Rule::enum(Gender::class)],
            'mobile_number' => ['required', 'string', 'max:20'],
            'alternate_number' => ['nullable', 'string', 'max:20'],
            'email' => UserEmailRules::rules(
                exceptUserId: null,
                membershipType: MembershipRole::MainMember->value,
                committeeRole: null,
            ),
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
            'effective_date.required' => __('messages.houses_transfer_effective_date_required'),
            'transfer_type.required' => __('messages.houses_transfer_type_required'),
            'first_name.required' => __('messages.users_first_name_required'),
            'last_name.required' => __('messages.users_last_name_required'),
            'gender.required' => __('messages.users_gender_required'),
            'mobile_number.required' => __('messages.users_mobile_required'),
        ];
    }
}
