<?php

namespace App\Http\Requests\Admin\Finance;

use Illuminate\Foundation\Http\FormRequest;

class OpenEditMaintenanceChargeRequest extends FormRequest
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
            'maintenance_charge_id' => ['required', 'integer', 'exists:maintenance_charge_settings,id'],
        ];
    }
}
