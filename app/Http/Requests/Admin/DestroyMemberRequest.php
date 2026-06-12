<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DestroyMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('members.delete') ?? false;
    }

    public function rules(): array
    {
        return [
            'member_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
