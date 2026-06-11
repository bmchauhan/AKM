<?php

namespace App\Enums;

enum MembershipRole: string
{
    case MainMember = 'main_member';
    case FamilyMember = 'family_member';
    case RentalMember = 'rental_member';
}
