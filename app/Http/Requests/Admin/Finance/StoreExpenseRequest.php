<?php

namespace App\Http\Requests\Admin\Finance;

use App\Http\Requests\Admin\Finance\Concerns\ValidatesExpensePayload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreExpenseRequest extends FormRequest
{
    use ValidatesExpensePayload;

    public function authorize(): bool
    {
        return $this->user()?->can('finance.create') ?? false;
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
}
