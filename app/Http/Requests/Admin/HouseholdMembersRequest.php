<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class HouseholdMembersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users_all.read') ?? false;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
