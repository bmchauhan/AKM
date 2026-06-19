<?php

namespace App\Http\Requests\Admin\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmailSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('settings_email.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'emails_enabled' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'emails_enabled' => $this->boolean('emails_enabled'),
        ]);
    }
}
