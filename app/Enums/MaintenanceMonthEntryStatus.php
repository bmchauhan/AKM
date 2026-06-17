<?php

namespace App\Enums;

enum MaintenanceMonthEntryStatus: string
{
    case Pending = 'pending';
    case Due = 'due';
    case Partial = 'partial';
    case Paid = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('messages.finance_ledger_status_pending'),
            self::Due => __('messages.finance_ledger_status_due'),
            self::Partial => __('messages.finance_ledger_status_partial'),
            self::Paid => __('messages.finance_ledger_status_paid'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-[#E6EBF4] text-[#080D21]',
            self::Due => 'bg-[#E5989B]/25 text-[#AB1E23]',
            self::Partial => 'bg-[#E6C280]/35 text-[#080D21]',
            self::Paid => 'bg-[#E6C280]/50 text-[#080D21]',
        };
    }
}
