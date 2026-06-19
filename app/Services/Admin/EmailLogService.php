<?php

namespace App\Services\Admin;

use App\Enums\EmailLogStatus;
use App\Enums\EmailMailType;
use App\Enums\EmailSkipReason;
use App\Models\EmailLog;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EmailLogService
{
    public function recordSent(
        EmailMailType $mailType,
        string $recipientEmail,
        string $subject,
        ?User $subjectUser = null,
        ?User $recipientUser = null,
        ?User $triggeredBy = null,
    ): EmailLog {
        return EmailLog::query()->create([
            'mail_type' => $mailType,
            'recipient_email' => $recipientEmail,
            'subject' => $subject,
            'status' => EmailLogStatus::Sent,
            'subject_user_id' => $subjectUser?->id,
            'recipient_user_id' => $recipientUser?->id,
            'triggered_by_user_id' => $triggeredBy?->id,
            'created_at' => now(),
        ]);
    }

    public function recordSkipped(
        EmailMailType $mailType,
        string $recipientEmail,
        string $subject,
        EmailSkipReason $reason,
        ?User $subjectUser = null,
        ?User $recipientUser = null,
        ?User $triggeredBy = null,
    ): EmailLog {
        return EmailLog::query()->create([
            'mail_type' => $mailType,
            'recipient_email' => $recipientEmail,
            'subject' => $subject,
            'status' => EmailLogStatus::Skipped,
            'skip_reason' => $reason,
            'subject_user_id' => $subjectUser?->id,
            'recipient_user_id' => $recipientUser?->id,
            'triggered_by_user_id' => $triggeredBy?->id,
            'created_at' => now(),
        ]);
    }

    public function recordFailed(
        EmailMailType $mailType,
        string $recipientEmail,
        string $subject,
        string $errorMessage,
        ?User $subjectUser = null,
        ?User $recipientUser = null,
        ?User $triggeredBy = null,
    ): EmailLog {
        return EmailLog::query()->create([
            'mail_type' => $mailType,
            'recipient_email' => $recipientEmail,
            'subject' => $subject,
            'status' => EmailLogStatus::Failed,
            'error_message' => $errorMessage,
            'subject_user_id' => $subjectUser?->id,
            'recipient_user_id' => $recipientUser?->id,
            'triggered_by_user_id' => $triggeredBy?->id,
            'created_at' => now(),
        ]);
    }

    public function paginated(int $perPage = 20): LengthAwarePaginator
    {
        return EmailLog::query()
            ->with(['subjectUser', 'recipientUser', 'triggeredBy'])
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }
}
