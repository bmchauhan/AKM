<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SyncRolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('settings.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'roles' => ['present', 'array'],
            'roles.*.id' => ['nullable', 'integer', 'exists:roles,id'],
            'roles.*.name' => ['required', 'string', 'max:100'],
            'roles.*.short_form' => ['required', 'string', 'max:20', 'regex:/^[A-Z0-9]+$/'],
            'roles.*.slug' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/'],
            'roles.*.description' => ['nullable', 'string', 'max:255'],
            'deleted_ids' => ['nullable', 'array'],
            'deleted_ids.*' => ['integer', 'exists:roles,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $roles = $this->input('roles', []);
            $slugs = [];
            $shortForms = [];

            foreach ($roles as $index => $role) {
                $slug = $role['slug'] ?? '';
                $shortForm = strtoupper($role['short_form'] ?? '');

                if (isset($slugs[$slug])) {
                    $validator->errors()->add(
                        "roles.{$index}.slug",
                        __('messages.roles_slug_duplicate'),
                    );
                }

                if (isset($shortForms[$shortForm])) {
                    $validator->errors()->add(
                        "roles.{$index}.short_form",
                        __('messages.roles_short_form_duplicate'),
                    );
                }

                $slugs[$slug] = true;
                $shortForms[$shortForm] = true;
            }
        });
    }

    public function messages(): array
    {
        return [
            'roles.*.name.required' => __('messages.roles_name_required'),
            'roles.*.short_form.required' => __('messages.roles_short_form_required'),
            'roles.*.short_form.regex' => __('messages.roles_short_form_format'),
            'roles.*.slug.required' => __('messages.roles_slug_required'),
            'roles.*.slug.regex' => __('messages.roles_slug_format'),
        ];
    }
}
