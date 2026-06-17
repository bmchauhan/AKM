<?php

namespace App\Http\Requests\Admin\Finance;

use Illuminate\Foundation\Http\FormRequest;

class OpenEditMaintenanceLedgerEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('finance.update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'entry_id' => ['required', 'integer', 'exists:maintenance_monthly_entries,id'],
            'return_context' => ['nullable', 'string', 'in:house'],
            'house' => ['nullable', 'string', 'max:50'],
            'status_filter' => ['nullable', 'string', 'max:30'],
        ];
    }
}
