<?php

namespace App\Enums;

enum FinancePaymentReceiptKind: string
{
    case Maintenance = 'maintenance';
    case Collection = 'collection';
    case WorkerSalary = 'worker_salary';

    public function label(): string
    {
        return match ($this) {
            self::Maintenance => __('messages.finance_collection_maintenance'),
            self::Collection => __('messages.finance_receipt_kind_collection'),
            self::WorkerSalary => __('messages.finance_receipt_kind_worker_salary'),
        };
    }
}
