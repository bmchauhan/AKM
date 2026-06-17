<?php

namespace App\Enums;

enum MaintenanceChargeStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Active => __('messages.finance_maintenance_status_active'),
            self::Inactive => __('messages.finance_maintenance_status_inactive'),
        };
    }
}
