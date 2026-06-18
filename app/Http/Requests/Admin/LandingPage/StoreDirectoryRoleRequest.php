<?php

namespace App\Http\Requests\Admin\LandingPage;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDirectoryRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('landing_page_directory_roles.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'slug' => ['nullable', 'string', 'max:60', 'alpha_dash', Rule::unique('useful_directory_roles', 'slug')],
            'name_en' => ['required', 'string', 'max:120'],
            'name_hi' => ['nullable', 'string', 'max:120'],
            'name_gu' => ['nullable', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'supports_committee_link' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
