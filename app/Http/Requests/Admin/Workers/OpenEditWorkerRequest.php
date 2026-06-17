<?php

namespace App\Http\Requests\Admin\Workers;

use Illuminate\Foundation\Http\FormRequest;

class OpenEditWorkerRequest extends FormRequest
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
        ];
    }
}
