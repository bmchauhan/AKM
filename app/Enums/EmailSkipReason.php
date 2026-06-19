<?php

namespace App\Enums;

enum EmailSkipReason: string
{
    case EmailsDisabled = 'emails_disabled';
    case NoRecipientEmail = 'no_recipient_email';

    public function label(): string
    {
        return match ($this) {
            self::EmailsDisabled => __('messages.email_skip_reason_disabled'),
            self::NoRecipientEmail => __('messages.email_skip_reason_no_recipient'),
        };
    }
}
