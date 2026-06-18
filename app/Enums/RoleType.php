<?php

namespace App\Enums;

enum RoleType: string
{
    case SuperAdmin = 'super_admin';
    case Committee = 'committee';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => __('messages.roles_type_super_admin'),
            self::Committee => __('messages.roles_type_committee'),
        };
    }
}
