<?php

namespace App\Enums;

enum FinanceCollectionType: string
{
    case Maintenance = 'maintenance';
    case ClubhouseBooking = 'clubhouse_booking';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Maintenance => __('messages.finance_collection_maintenance'),
            self::ClubhouseBooking => __('messages.finance_collection_clubhouse'),
            self::Other => __('messages.finance_collection_other'),
        };
    }
}
