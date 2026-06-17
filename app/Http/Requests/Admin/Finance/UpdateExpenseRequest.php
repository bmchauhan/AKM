<?php

namespace App\Http\Requests\Admin\Finance;

use App\Http\Requests\Admin\Finance\Concerns\ValidatesExpensePayload;
use App\Services\Admin\AdminFinanceExpenseService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateExpenseRequest extends FormRequest
{
    use ValidatesExpensePayload;

    public function authorize(): bool
    {
        return $this->user()?->can('finance.update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->expenseRules();
    }

    public function withValidator(Validator $validator): void
    {
        $this->prepareExpenseValidation($validator);
    }

    public function editingExpenseId(): ?int
    {
        $id = session(AdminFinanceExpenseService::SESSION_EDITING_EXPENSE);

        return $id ? (int) $id : null;
    }
}
