<?php

namespace App\Http\Requests\Admin\LandingPage;

use Illuminate\Foundation\Http\FormRequest;

class DestroyDirectoryRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('landing_page_directory_roles.delete') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'role_id' => ['required', 'integer', 'exists:useful_directory_roles,id'],
        ];
    }
}
