<?php

namespace App\Http\Requests\Admin\Workers;

use App\Enums\WorkerType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('workers.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'worker_type' => ['required', 'string', Rule::enum(WorkerType::class)],
            'name' => ['required', 'string', 'max:255'],
            'mobile_number' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
            'joined_on' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'monthly_salary' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'salary_effective_from' => ['nullable', 'date'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
        ];
    }
}
