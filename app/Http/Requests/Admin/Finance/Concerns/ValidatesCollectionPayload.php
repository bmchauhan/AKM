<?php

namespace App\Http\Requests\Admin\Finance\Concerns;

use App\Enums\FinanceCollectionType;
use App\Enums\FinancePaymentMode;
use App\Enums\MembershipRole;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

trait ValidatesCollectionPayload
{
    /**
     * @return array<string, mixed>
     */
    protected function collectionRules(): array
    {
        $isMaintenance = fn () => $this->input('collection_type') === FinanceCollectionType::Maintenance->value;

        $shared = [
            'collection_type' => [
                'required',
                'string',
                Rule::enum(FinanceCollectionType::class),
            ],
            'received_on' => ['required', 'date', 'before_or_equal:today'],
            'payment_mode' => ['nullable', 'string', Rule::enum(FinancePaymentMode::class)],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];

        if ($isMaintenance()) {
            return array_merge($shared, [
                'main_member_id' => [
                    'required',
                    'integer',
                    Rule::exists('users', 'id')->where('membership_type', MembershipRole::MainMember->value),
                ],
                'maintenance_base_amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
                'maintenance_charge_setting_id' => ['nullable', 'integer', 'exists:maintenance_charge_settings,id'],
                'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            ]);
        }

        return array_merge($shared, [
            'main_member_id' => ['nullable'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
        ]);
    }

    protected function prepareCollectionValidation(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->input('collection_type') !== FinanceCollectionType::Maintenance->value) {
                return;
            }

            $base = round((float) $this->input('maintenance_base_amount', 0), 2);
            $amount = round((float) $this->input('amount', 0), 2);

            if ($amount > $base + 0.01) {
                $validator->errors()->add('amount', __('messages.finance_collection_amount_exceeds_charge'));
            }
        });
    }
}
