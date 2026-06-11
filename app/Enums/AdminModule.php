<?php

namespace App\Enums;

enum AdminModule: string
{
    case Users = 'users';
    case Members = 'members';
    case Settings = 'settings';
}
