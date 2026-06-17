<?php

namespace App\Http\Requests\Admin\Finance;

use Illuminate\Foundation\Http\FormRequest;

class OpenEditExpenseRequest extends FormRequest
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
            'expense_id' => ['required', 'integer', 'exists:finance_expenses,id'],
        ];
    }
}
