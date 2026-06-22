<?php

namespace App\Enums;

enum VisitorEntryStatus: string
{
    case Active = 'active';
    case Exited = 'exited';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Exited => 'Exited',
            self::Cancelled => 'Cancelled',
        };
    }
}
