<?php

namespace App\Http\Requests\Admin\Finance\Concerns;

use App\Enums\FinanceExpenseTag;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

trait ValidatesExpensePayload
{
    /**
     * @return array<string, mixed>
     */
    protected function expenseRules(): array
    {
        $tag = FinanceExpenseTag::tryFrom((string) $this->input('expense_tag'));
        $requiresWorker = $tag?->requiresWorker() ?? false;

        $shared = [
            'expense_tag' => [
                'required',
                'string',
                Rule::enum(FinanceExpenseTag::class),
            ],
            'paid_on' => ['required', 'date', 'before_or_equal:today'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => [
                Rule::requiredIf(fn () => $this->input('expense_tag') === FinanceExpenseTag::Others->value),
                'nullable',
                'string',
                'max:2000',
            ],
        ];

        if ($requiresWorker) {
            return array_merge($shared, [
                'worker_id' => ['required', 'integer', 'exists:workers,id'],
                'salary_base_amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
                'salary_adjustment' => ['nullable', 'numeric', 'min:-9999999999.99', 'max:9999999999.99'],
                'salary_adjustment_note' => ['nullable', 'string', 'max:500'],
                'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
                'payee_name' => ['nullable', 'string', 'max:255'],
            ]);
        }

        return array_merge($shared, [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'payee_name' => ['nullable', 'string', 'max:255'],
        ]);
    }

    protected function prepareExpenseValidation(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $tag = FinanceExpenseTag::tryFrom((string) $this->input('expense_tag'));

            if (! $tag?->requiresWorker()) {
                return;
            }

            $base = round((float) $this->input('salary_base_amount', 0), 2);
            $adjustment = round((float) $this->input('salary_adjustment', 0), 2);
            $amount = round((float) $this->input('amount', 0), 2);

            if (abs($amount - ($base + $adjustment)) > 0.01) {
                $validator->errors()->add('amount', __('messages.finance_expense_amount_mismatch'));
            }
        });
    }
}
