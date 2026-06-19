<?php

namespace App\Enums;

enum EmailMailType: string
{
    case UserAccountCreated = 'user_account_created';
    case FamilyMemberCredentialsToMain = 'family_member_credentials_to_main';

    public function label(): string
    {
        return match ($this) {
            self::UserAccountCreated => __('messages.email_log_type_user_account_created'),
            self::FamilyMemberCredentialsToMain => __('messages.email_log_type_family_member_to_main'),
        };
    }
}
