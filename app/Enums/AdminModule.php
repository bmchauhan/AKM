<?php

namespace App\Enums;

enum AdminModule: string
{
    case Users = 'users';
    case Members = 'members';
    case Finance = 'finance';
    case Workers = 'workers';
    case Settings = 'settings';
    case LandingPage = 'landing_page';
}
