<?php

namespace App\Enums;

enum FinancePaymentMode: string
{
    case Cash = 'cash';
    case Upi = 'upi';
    case Cheque = 'cheque';
    case BankTransfer = 'bank_transfer';

    public function label(): string
    {
        return match ($this) {
            self::Cash => __('messages.finance_payment_cash'),
            self::Upi => __('messages.finance_payment_upi'),
            self::Cheque => __('messages.finance_payment_cheque'),
            self::BankTransfer => __('messages.finance_payment_bank_transfer'),
        };
    }
}
