<?php

namespace App\Http\Requests\Admin\Finance;

use Illuminate\Foundation\Http\FormRequest;

class DestroyCollectionRequest extends FormRequest
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
            'collection_id' => ['required', 'integer', 'exists:finance_collections,id'],
        ];
    }
}
