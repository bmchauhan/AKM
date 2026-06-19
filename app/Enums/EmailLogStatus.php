<?php

namespace App\Enums;

enum EmailLogStatus: string
{
    case Sent = 'sent';
    case Skipped = 'skipped';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Sent => __('messages.email_log_status_sent'),
            self::Skipped => __('messages.email_log_status_skipped'),
            self::Failed => __('messages.email_log_status_failed'),
        };
    }
}
