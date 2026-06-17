<?php

namespace App\Http\Requests\Admin\Finance;

use App\Enums\FinanceCollectionType;
use App\Http\Requests\Admin\Finance\Concerns\ValidatesCollectionPayload;
use App\Services\Admin\AdminFinanceCollectionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateCollectionRequest extends FormRequest
{
    use ValidatesCollectionPayload;

    public function authorize(): bool
    {
        return $this->user()?->can('finance.update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->collectionRules();
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

    public function editingCollectionId(): ?int
    {
        $id = session(AdminFinanceCollectionService::SESSION_EDITING_COLLECTION);

        return $id ? (int) $id : null;
    }
}
