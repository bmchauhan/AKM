<?php

namespace App\Http\Requests\Admin\Finance;

use App\Enums\MaintenanceChargeStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMaintenanceChargeRequest extends FormRequest
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
            'status' => ['required', 'string', Rule::enum(MaintenanceChargeStatus::class)],
            'end_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
