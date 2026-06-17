<?php

namespace App\Http\Requests\Admin\Finance;

use App\Enums\FinancePaymentMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMaintenanceLedgerEntryRequest extends FormRequest
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
            'amount_paid' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'paid_on' => ['nullable', 'date', 'before_or_equal:today'],
            'payment_mode' => ['nullable', 'string', Rule::enum(FinancePaymentMode::class)],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'return_context' => ['nullable', 'string', 'in:house'],
            'house' => ['nullable', 'string', 'max:50'],
            'status_filter' => ['nullable', 'string', 'max:30'],
        ];
    }
}
