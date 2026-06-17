<?php

namespace App\Http\Requests\Admin\Finance;

use Illuminate\Foundation\Http\FormRequest;

class DestroyExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('finance.delete') ?? false;
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
