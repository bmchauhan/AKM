<?php

namespace App\Http\Requests\Admin\Finance;

use App\Enums\FinanceCollectionType;
use App\Http\Requests\Admin\Finance\Concerns\ValidatesCollectionPayload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCollectionRequest extends FormRequest
{
    use ValidatesCollectionPayload;

    public function authorize(): bool
    {
        return $this->user()?->can('finance.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = $this->collectionRules();
        $rules['collection_type'][] = Rule::notIn([FinanceCollectionType::Maintenance->value]);

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'collection_type.not_in' => __('messages.finance_collection_maintenance_use_ledger'),
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('collection_type') !== FinanceCollectionType::Maintenance->value) {
            $this->merge(['main_member_id' => null]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $this->prepareCollectionValidation($validator);
    }
}
