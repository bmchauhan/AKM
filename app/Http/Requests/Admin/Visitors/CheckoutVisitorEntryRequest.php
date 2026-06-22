<?php

namespace App\Http\Requests\Admin\Visitors;

use App\Enums\VisitorEntryStatus;
use App\Models\VisitorEntry;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutVisitorEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('visitors_log.update') ?? false;
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

    public function visitorEntry(): VisitorEntry
    {
        return VisitorEntry::query()->findOrFail($this->integer('visitor_entry_id'));
    }
}
