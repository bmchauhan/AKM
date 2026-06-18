<?php

namespace App\Http\Requests\Admin\LandingPage;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDirectoryRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('landing_page_directory_roles.update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name_en' => ['required', 'string', 'max:120'],
            'name_hi' => ['nullable', 'string', 'max:120'],
            'name_gu' => ['nullable', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'supports_committee_link' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
