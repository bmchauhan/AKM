<?php

namespace App\Enums;

enum MembershipRole: string
{
    case MainMember = 'main_member';
    case FamilyMember = 'family_member';
    case RentalMember = 'rental_member';

    public function label(): string
    {
        return match ($this) {
            self::MainMember => 'Main Member',
            self::FamilyMember => 'Family Member',
            self::RentalMember => 'Rental Member',
        };
    }

    public function shortForm(): string
    {
        return match ($this) {
            self::MainMember => 'MM',
            self::FamilyMember => 'FM',
            self::RentalMember => 'RM',
        };
    }
}
