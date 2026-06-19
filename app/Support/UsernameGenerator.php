<?php

namespace App\Support;

use App\Enums\Gender;
use App\Repositories\Contracts\UserRepositoryInterface;

final class UsernameGenerator
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function generate(
        string $firstName,
        string $gender,
        string $houseType,
        string $houseNumber,
        ?int $exceptUserId = null,
    ): string {
        $base = $this->buildBase($firstName, $gender, $houseType, $houseNumber);
        $candidate = $base;
        $suffix = 1;

        while ($this->users->usernameExists($candidate, $exceptUserId)) {
            $candidate = $base.$suffix;
            $suffix++;
        }

        return $candidate;
    }

    public function preview(string $firstName, string $gender, string $houseType, string $houseNumber): string
    {
        if (! filled($gender) || ! filled($houseType) || ! filled($houseNumber)) {
            return '';
        }

        return $this->buildBase($firstName, $gender, $houseType, $houseNumber);
    }

    private function buildBase(string $firstName, string $gender, string $houseType, string $houseNumber): string
    {
        $alpha = preg_replace('/[^a-zA-Z]/', '', $firstName) ?: 'USER';
        $prefix = strtoupper(str_pad(substr($alpha, 0, 4), 4, 'X'));

        $genderLetter = match ($gender) {
            Gender::Male->value => 'M',
            Gender::Female->value => 'F',
            default => 'O',
        };

        return $prefix.$genderLetter.strtoupper($houseType).trim($houseNumber);
    }
}
