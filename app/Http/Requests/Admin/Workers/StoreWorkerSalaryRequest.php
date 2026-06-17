<?php

namespace App\Http\Requests\Admin\Workers;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkerSalaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('workers.update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'worker_id' => ['required', 'integer', 'exists:workers,id'],
            'monthly_salary' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'effective_from' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
