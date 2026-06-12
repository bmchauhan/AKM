<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class OpenMemberEditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('members.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'member_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
