<?php

namespace App\Support;

use App\Models\User;

final readonly class CreatedUserResult
{
    public function __construct(
        public User $user,
        public bool $credentialsEmailed = false,
        public bool $credentialsEmailedToMainMember = false,
        public bool $mainMemberCredentialsAttempted = false,
        public bool $credentialsSkippedDueToDisabled = false,
    ) {}
}
