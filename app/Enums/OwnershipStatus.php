<?php

namespace App\Enums;

enum OwnershipStatus: string
{
    case Active = 'active';
    case FormerOwner = 'former_owner';

    public function label(): string
    {
        return match ($this) {
            self::Active => __('messages.ownership_status_active'),
            self::FormerOwner => __('messages.ownership_status_former_owner'),
        };
    }
}
