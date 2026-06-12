<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SelectHouseholdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('members.read') ?? false;
    }

    public function rules(): array
    {
        return [
            'main_member_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'main_member_id.required' => __('messages.members_main_member_required'),
        ];
    }
}
