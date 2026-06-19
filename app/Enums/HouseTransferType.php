<?php

namespace App\Enums;

enum HouseTransferType: string
{
    case Sale = 'sale';
    case Gift = 'gift';
    case Correction = 'correction';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Sale => __('messages.house_transfer_type_sale'),
            self::Gift => __('messages.house_transfer_type_gift'),
            self::Correction => __('messages.house_transfer_type_correction'),
            self::Other => __('messages.house_transfer_type_other'),
        };
    }
}
