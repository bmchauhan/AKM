<?php

namespace App\Enums;

enum EmailMailType: string
{
    case UserAccountCreated = 'user_account_created';
    case FamilyMemberCredentialsToMain = 'family_member_credentials_to_main';
    case RentalVisitorToMainMember = 'rental_visitor_to_main_member';

    public function label(): string
    {
        return match ($this) {
            self::UserAccountCreated => __('messages.email_log_type_user_account_created'),
            self::FamilyMemberCredentialsToMain => __('messages.email_log_type_family_member_to_main'),
            self::RentalVisitorToMainMember => __('messages.email_log_type_rental_visitor_to_main'),
        };
    }
}
