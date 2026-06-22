<?php

namespace App\Services\Admin;

use App\Enums\EmailMailType;
use App\Enums\EmailSkipReason;
use App\Enums\MembershipRole;
use App\Mail\RentalVisitorToMainMemberMail;
use App\Models\User;
use App\Models\VisitorEntry;
use Illuminate\Support\Facades\Mail;
use Throwable;

class VisitorEntryMailService
{
    public function __construct(
        private readonly EmailSettingsService $emailSettings,
        private readonly EmailLogService $emailLogs,
    ) {}

    public function notifyMainMemberOfRentalVisit(VisitorEntry $entry, ?User $triggeredBy = null): bool
    {
        $entry->loadMissing(['houseUnit', 'host', 'loggedBy']);

        if ($entry->host_type !== MembershipRole::RentalMember->value) {
            return false;
        }

        $rentalMember = $entry->host;

        if (! $rentalMember) {
            return false;
        }

        $mainMember = $rentalMember->mainMember;

        if (! $mainMember || ! filled($mainMember->email)) {
            return false;
        }

        $mailType = EmailMailType::RentalVisitorToMainMember;
        $subject = __('messages.visitors_rental_email_subject', [
            'house' => $entry->houseUnit?->label() ?? '—',
        ]);

        if (! $this->emailSettings->emailsEnabled()) {
            $this->emailLogs->recordSkipped(
                $mailType,
                $mainMember->email,
                $subject,
                EmailSkipReason::EmailsDisabled,
                $rentalMember,
                $mainMember,
                $triggeredBy,
            );

            return false;
        }

        try {
            Mail::to($mainMember->email)->send(new RentalVisitorToMainMemberMail(
                $mainMember,
                $rentalMember,
                $entry,
            ));

            $this->emailLogs->recordSent(
                $mailType,
                $mainMember->email,
                $subject,
                $rentalMember,
                $mainMember,
                $triggeredBy,
            );

            return true;
        } catch (Throwable $exception) {
            report($exception);

            $this->emailLogs->recordFailed(
                $mailType,
                $mainMember->email,
                $subject,
                $exception->getMessage(),
                $rentalMember,
                $mainMember,
                $triggeredBy,
            );

            return false;
        }
    }
}
