<?php

namespace App\Enums;

enum HouseType: string
{
    case A = 'A';
    case B = 'B';

    public function label(): string
    {
        return $this->value;
    }
}
