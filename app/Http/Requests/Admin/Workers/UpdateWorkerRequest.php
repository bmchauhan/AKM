<?php

namespace App\Http\Requests\Admin\Workers;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('workers.update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'mobile_number' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
            'joined_on' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['sometimes', 'boolean'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
