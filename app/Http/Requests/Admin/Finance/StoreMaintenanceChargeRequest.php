<?php

namespace App\Http\Requests\Admin\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaintenanceChargeRequest extends FormRequest
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
            'monthly_amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'effective_from' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
