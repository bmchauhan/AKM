<?php

namespace App\Enums;

enum Gender: string
{
    case Male = 'male';
    case Female = 'female';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Male => __('messages.gender_male'),
            self::Female => __('messages.gender_female'),
            self::Other => __('messages.gender_other'),
        };
    }
}
