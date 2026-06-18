<?php

namespace App\Enums;

/**
 * @deprecated Committee roles are defined in the `roles` table. Use CommitteeRoleRegistry instead.
 */
enum CommitteeRole: string
{
    case ChiefCommitteeMember = 'chief_committee_member';
    case ViceChiefCommitteeMember = 'vice_chief_committee_member';
    case FinanceCommitteeMember = 'finance_committee_member';
    case CommitteeMember = 'committee_member';

    public function label(): string
    {
        return match ($this) {
            self::ChiefCommitteeMember => 'Chief Committee Member',
            self::ViceChiefCommitteeMember => 'Vice Chief Committee Member',
            self::FinanceCommitteeMember => 'Finance Committee Member',
            self::CommitteeMember => 'Committee Member',
        };
    }

    public function shortForm(): string
    {
        return match ($this) {
            self::ChiefCommitteeMember => 'CCM',
            self::ViceChiefCommitteeMember => 'VCCM',
            self::FinanceCommitteeMember => 'FCM',
            self::CommitteeMember => 'CM',
        };
    }
}
