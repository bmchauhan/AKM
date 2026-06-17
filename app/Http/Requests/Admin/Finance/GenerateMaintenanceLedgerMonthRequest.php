<?php

namespace App\Http\Requests\Admin\Finance;

use Illuminate\Foundation\Http\FormRequest;

class GenerateMaintenanceLedgerMonthRequest extends FormRequest
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
            'month' => ['required', 'date_format:Y-m'],
        ];
    }
}
