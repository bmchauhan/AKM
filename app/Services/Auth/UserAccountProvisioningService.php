<?php

namespace App\Services\Auth;

use App\Enums\EmailMailType;
use App\Enums\EmailSkipReason;
use App\Mail\FamilyMemberCredentialsToMainMemberMail;
use App\Mail\UserAccountCreatedMail;
use App\Models\User;
use App\Services\Admin\EmailLogService;
use App\Services\Admin\EmailSettingsService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class UserAccountProvisioningService
{
    public function __construct(
        private readonly EmailSettingsService $emailSettings,
        private readonly EmailLogService $emailLogs,
    ) {}

    public function generatePassword(): string
    {
        return Str::password(12, symbols: false);
    }

    public function notifyCredentials(User $user, string $plainPassword, ?User $triggeredBy = null): bool
    {
        $mailType = EmailMailType::UserAccountCreated;
        $subject = __('messages.user_welcome_email_subject');

        if (! filled($user->email)) {
            return false;
        }

        if (! $this->emailSettings->emailsEnabled()) {
            $this->emailLogs->recordSkipped(
                $mailType,
                $user->email,
                $subject,
                EmailSkipReason::EmailsDisabled,
                $user,
                $user,
                $triggeredBy,
            );

            return false;
        }

        try {
            Mail::to($user->email)->send(new UserAccountCreatedMail($user, $plainPassword));

            $this->emailLogs->recordSent(
                $mailType,
                $user->email,
                $subject,
                $user,
                $user,
                $triggeredBy,
            );

            return true;
        } catch (Throwable $exception) {
            report($exception);

            $this->emailLogs->recordFailed(
                $mailType,
                $user->email,
                $subject,
                $exception->getMessage(),
                $user,
                $user,
                $triggeredBy,
            );

            return false;
        }
    }

    public function notifyFamilyMemberCredentialsToMainMember(
        User $familyMember,
        User $mainMember,
        string $plainPassword,
        ?User $triggeredBy = null,
    ): bool {
        $mailType = EmailMailType::FamilyMemberCredentialsToMain;
        $subject = __('messages.user_family_member_credentials_subject');

        if (! filled($mainMember->email)) {
            return false;
        }

        if (! $this->emailSettings->emailsEnabled()) {
            $this->emailLogs->recordSkipped(
                $mailType,
                $mainMember->email,
                $subject,
                EmailSkipReason::EmailsDisabled,
                $familyMember,
                $mainMember,
                $triggeredBy,
            );

            return false;
        }

        try {
            Mail::to($mainMember->email)->send(new FamilyMemberCredentialsToMainMemberMail(
                $mainMember,
                $familyMember,
                $plainPassword,
            ));

            $this->emailLogs->recordSent(
                $mailType,
                $mainMember->email,
                $subject,
                $familyMember,
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
                $familyMember,
                $mainMember,
                $triggeredBy,
            );

            return false;
        }
    }
}
