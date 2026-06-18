<?php

namespace App\Http\Requests\Admin\LandingPage;

use Illuminate\Foundation\Http\FormRequest;

class DestroyUsefulDirectoryContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('landing_page_useful_directory.delete') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'contact_id' => ['required', 'integer', 'exists:useful_directory_contacts,id'],
        ];
    }
}
