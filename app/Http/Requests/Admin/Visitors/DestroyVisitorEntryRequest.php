<?php

namespace App\Http\Requests\Admin\Visitors;

use Illuminate\Foundation\Http\FormRequest;

class DestroyVisitorEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('visitors_all.delete') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'visitor_entry_id' => ['required', 'integer', 'exists:visitor_entries,id'],
        ];
    }
}
