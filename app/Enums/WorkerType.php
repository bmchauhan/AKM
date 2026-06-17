<?php

namespace App\Enums;

enum WorkerType: string
{
    case Sweeper = 'sweeper';
    case GarbageCollector = 'garbage_collector';
    case SecurityGuard = 'security_guard';
    case Gardener = 'gardener';

    public function label(): string
    {
        return match ($this) {
            self::Sweeper => __('messages.workers_type_sweeper'),
            self::GarbageCollector => __('messages.workers_type_garbage'),
            self::SecurityGuard => __('messages.workers_type_security'),
            self::Gardener => __('messages.workers_type_gardener'),
        };
    }

    public function expenseTag(): FinanceExpenseTag
    {
        return match ($this) {
            self::Sweeper => FinanceExpenseTag::SocietySweeperPayment,
            self::GarbageCollector => FinanceExpenseTag::GarbageCollectorPayment,
            self::SecurityGuard => FinanceExpenseTag::SecurityPayment,
            self::Gardener => FinanceExpenseTag::GardenerPayment,
        };
    }

    public static function fromExpenseTag(FinanceExpenseTag $tag): ?self
    {
        return match ($tag) {
            FinanceExpenseTag::SocietySweeperPayment => self::Sweeper,
            FinanceExpenseTag::GarbageCollectorPayment => self::GarbageCollector,
            FinanceExpenseTag::SecurityPayment => self::SecurityGuard,
            FinanceExpenseTag::GardenerPayment => self::Gardener,
            default => null,
        };
    }
}
