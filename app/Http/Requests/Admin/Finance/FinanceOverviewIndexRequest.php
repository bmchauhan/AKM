<?php

namespace App\Http\Requests\Admin\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinanceOverviewIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('finance_overview.read') ?? false;
    }

    public function rules(): array
    {
        return [
            'period' => ['nullable', 'string', Rule::in(['month', 'last_month', 'quarter', 'year', 'all', 'custom'])],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ];
    }

    /**
     * @return array{period: string, from: ?string, to: ?string}
     */
    public function filters(): array
    {
        return [
            'period' => $this->string('period')->toString() ?: 'year',
            'from' => $this->string('from')->toString() ?: null,
            'to' => $this->string('to')->toString() ?: null,
        ];
    }
}
