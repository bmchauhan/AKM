<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'password_confirmation' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => __('messages.password_current_required'),
            'current_password.current_password' => __('messages.password_current_invalid'),
            'password.required' => __('messages.password_new_required'),
            'password.confirmed' => __('messages.password_confirm_mismatch'),
            'password_confirmation.required' => __('messages.password_confirm_required'),
        ];
    }
}
