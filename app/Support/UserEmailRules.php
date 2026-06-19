<?php

namespace App\Support;

use App\Enums\MembershipRole;
use Illuminate\Validation\Rule;

final class UserEmailRules
{
    public static function requiresEmail(?string $membershipType, ?string $committeeRole, bool $isSuperAdmin = false): bool
    {
        if ($isSuperAdmin) {
            return true;
        }

        if (filled($committeeRole)) {
            return true;
        }

        return $membershipType === MembershipRole::MainMember->value;
    }

    /**
     * @return list<\Illuminate\Contracts\Validation\ValidationRule|string>
     */
    public static function rules(?int $exceptUserId = null, ?string $membershipType = null, ?string $committeeRole = null, bool $isSuperAdmin = false): array
    {
        $unique = $exceptUserId
            ? Rule::unique('users', 'email')->ignore($exceptUserId)
            : Rule::unique('users', 'email');

        return [
            self::requiresEmail($membershipType, $committeeRole, $isSuperAdmin) ? 'required' : 'nullable',
            'email',
            'max:255',
            $unique,
        ];
    }

    public static function normalize(?string $email): ?string
    {
        if (! filled($email)) {
            return null;
        }

        return trim($email);
    }
}
