<?php

namespace App\Enums;

enum AdminModule: string
{
    case Users = 'users';
    case Members = 'members';
    case Houses = 'houses';
    case Finance = 'finance';
    case Workers = 'workers';
    case Visitors = 'visitors';
    case Settings = 'settings';
    case LandingPage = 'landing_page';
}
