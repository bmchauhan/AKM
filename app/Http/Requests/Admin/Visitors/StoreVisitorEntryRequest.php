<?php

namespace App\Http\Requests\Admin\Visitors;

use App\Enums\MembershipRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreVisitorEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('visitors_log.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'house_unit_id' => ['required', 'integer', 'exists:house_units,id'],
            'host_user_id' => ['required', 'integer', 'exists:users,id'],
            'visitor_name' => ['required', 'string', 'max:255'],
            'visitor_contact' => ['required', 'string', 'max:20'],
            'party_size' => ['required', 'integer', 'min:1', 'max:20'],
            'male_count' => ['nullable', 'integer', 'min:0', 'max:20'],
            'female_count' => ['nullable', 'integer', 'min:0', 'max:20'],
            'children_count' => ['nullable', 'integer', 'min:0', 'max:20'],
            'vehicle_number' => ['nullable', 'string', 'max:32'],
            'purpose' => ['nullable', 'string', 'max:64'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'id_proof' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'photo' => [
                Rule::requiredIf(fn () => $this->hostIsRental()),
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $male = (int) $this->input('male_count', 0);
            $female = (int) $this->input('female_count', 0);
            $children = (int) $this->input('children_count', 0);
            $partySize = (int) $this->input('party_size', 1);

            if ($male + $female + $children !== $partySize) {
                $validator->errors()->add('party_size', __('messages.visitors_party_size_mismatch'));
            }
        });
    }

    /**
     * @return array<string, int|string|null>
     */
    public function entryData(): array
    {
        return [
            'house_unit_id' => (int) $this->input('house_unit_id'),
            'host_user_id' => (int) $this->input('host_user_id'),
            'visitor_name' => $this->input('visitor_name'),
            'visitor_contact' => $this->input('visitor_contact'),
            'party_size' => (int) $this->input('party_size'),
            'male_count' => (int) $this->input('male_count', 0),
            'female_count' => (int) $this->input('female_count', 0),
            'children_count' => (int) $this->input('children_count', 0),
            'vehicle_number' => $this->input('vehicle_number'),
            'purpose' => $this->input('purpose'),
            'notes' => $this->input('notes'),
        ];
    }

    private function hostIsRental(): bool
    {
        $hostId = $this->integer('host_user_id');

        if ($hostId <= 0) {
            return false;
        }

        $host = User::query()->find($hostId);

        return $host?->membership_type === MembershipRole::RentalMember->value;
    }
}
