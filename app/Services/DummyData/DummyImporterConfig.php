<?php

namespace App\Services\DummyData;

use Illuminate\Support\Carbon;
use InvalidArgumentException;

final class DummyImporterConfig
{
    public function __construct(
        public readonly int $userCount,
        public readonly int $mainMemberCount,
        public readonly int $familyMin,
        public readonly int $familyMax,
        public readonly Carbon $financeFrom,
        public readonly Carbon $financeTo,
        public readonly float $maintenanceCharge,
        public readonly float $openingBalance,
        public readonly Carbon $openingBalanceFrom,
        public readonly bool $includeExpenses = true,
        public readonly int $workerCount = 0,
        public readonly float $workerSalaryMin = 10000,
        public readonly float $workerSalaryMax = 14000,
        public readonly bool $includeWorkerPayments = true,
    ) {
        if ($this->userCount < 0 || $this->mainMemberCount < 0) {
            throw new InvalidArgumentException('User and main member counts must be zero or greater.');
        }

        if ($this->workerCount < 0) {
            throw new InvalidArgumentException('Worker count must be zero or greater.');
        }

        if ($this->familyMin < 0 || $this->familyMax < 0 || $this->familyMin > $this->familyMax) {
            throw new InvalidArgumentException('Family member range is invalid.');
        }

        if ($this->workerSalaryMin < 0.01 || $this->workerSalaryMax < $this->workerSalaryMin) {
            throw new InvalidArgumentException('Worker salary range is invalid.');
        }

        if ($this->financeFrom->gt($this->financeTo)) {
            throw new InvalidArgumentException('Finance from date must be on or before the to date.');
        }

        if ($this->maintenanceCharge < 0.01) {
            throw new InvalidArgumentException('Maintenance charge must be at least 0.01.');
        }

        if ($this->openingBalance < 0) {
            throw new InvalidArgumentException('Opening balance cannot be negative.');
        }
    }

    /**
     * @return array{0: int, 1: int}
     */
    public static function parseFamilyRange(string $input): array
    {
        $input = trim($input);

        if (preg_match('/^(\d+)-(\d+)$/', $input, $matches)) {
            $min = (int) $matches[1];
            $max = (int) $matches[2];

            return [min($min, $max), max($min, $max)];
        }

        $count = max(0, (int) $input);

        return [$count, $count];
    }

    /**
     * @return array{0: float, 1: float}
     */
    public static function parseSalaryRange(string $input): array
    {
        $input = trim($input);

        if (preg_match('/^(\d+(?:\.\d+)?)-(\d+(?:\.\d+)?)$/', $input, $matches)) {
            $min = (float) $matches[1];
            $max = (float) $matches[2];

            return [min($min, $max), max($min, $max)];
        }

        $amount = max(0.01, (float) $input);

        return [$amount, $amount];
    }

    public function totalHousesNeeded(): int
    {
        return $this->userCount + $this->mainMemberCount;
    }
}
