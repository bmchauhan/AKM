<?php

namespace App\Http\Requests\Admin\Finance;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFundSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'opening_balance' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'opening_balance_effective_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
