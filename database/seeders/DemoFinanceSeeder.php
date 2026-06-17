<?php

namespace Database\Seeders;

use App\Enums\FinanceCollectionType;
use App\Enums\FinanceExpenseTag;
use App\Enums\FinancePaymentMode;
use App\Enums\MembershipRole;
use App\Models\FinanceCollection;
use App\Models\FinanceExpense;
use App\Models\SocietyFundSetting;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerSalaryRate;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoFinanceSeeder extends Seeder
{
    private const DEMO_COLLECTION_NOTE = 'Demo finance seed';

    private const DEMO_EXPENSE_NOTE = 'Demo finance seed';

    private const TARGET_COLLECTIONS = 2100;

    private const TARGET_EXPENSES = 2100;

    private const FAKER_SEED = 202606081;

    private ?Generator $faker = null;

    public function run(): void
    {
        if (FinanceCollection::query()->where('notes', self::DEMO_COLLECTION_NOTE)->exists()) {
            $this->command?->info('Demo finance collections already exist — skipping finance seed.');

            return;
        }

        $actor = $this->resolveActor();

        if (! $actor) {
            $this->command?->warn('No admin user found — cannot seed finance data.');

            return;
        }

        $mainMemberIds = User::query()
            ->where('membership_type', MembershipRole::MainMember->value)
            ->orderBy('id')
            ->pluck('id')
            ->all();

        if ($mainMemberIds === []) {
            $this->command?->warn('No main members found — run DemoDataSeeder first.');

            return;
        }

        $periodStart = now()->subYears(2)->startOfDay();
        $periodEnd = now()->endOfDay();
        $now = now();

        $this->seedOpeningBalance($actor, $periodStart->copy(), $now);

        $collectionCount = $this->seedCollections($mainMemberIds, $actor, $periodStart, $periodEnd, $now);
        $expenseCount = $this->seedExpenses($actor, $periodStart, $periodEnd, $now);

        $this->command?->info(sprintf(
            'Seeded %d demo collections and %d demo expenses (%s to %s).',
            $collectionCount,
            $expenseCount,
            $periodStart->format('d M Y'),
            $periodEnd->format('d M Y'),
        ));
    }

    private function resolveActor(): ?User
    {
        return User::query()
            ->where('email', 'sa@gmail.com')
            ->first()
            ?? User::query()
                ->where('committee_role', 'finance_committee_member')
                ->first();
    }

    private function seedOpeningBalance(User $actor, Carbon $effectiveDate, Carbon $now): void
    {
        if (SocietyFundSetting::query()->exists()) {
            return;
        }

        SocietyFundSetting::query()->create([
            'opening_balance' => 750000,
            'opening_balance_effective_date' => $effectiveDate->toDateString(),
            'notes' => 'Demo opening balance for society fund',
            'set_by_user_id' => $actor->id,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * @param  list<int>  $mainMemberIds
     */
    private function seedCollections(
        array $mainMemberIds,
        User $actor,
        Carbon $periodStart,
        Carbon $periodEnd,
        Carbon $now,
    ): int {
        $faker = $this->faker();
        $paymentModes = array_column(FinancePaymentMode::cases(), 'value');
        $rows = [];
        $count = 0;

        for ($index = 0; $index < self::TARGET_COLLECTIONS; $index++) {
            $roll = $faker->numberBetween(1, 100);
            $type = match (true) {
                $roll <= 72 => FinanceCollectionType::Maintenance,
                $roll <= 88 => FinanceCollectionType::ClubhouseBooking,
                default => FinanceCollectionType::Other,
            };

            $receivedOn = $this->randomDateBetween($periodStart, $periodEnd);

            $amount = match ($type) {
                FinanceCollectionType::Maintenance => $faker->randomElement([1800, 2000, 2200, 2500, 2800, 3000, 3200, 3500]),
                FinanceCollectionType::ClubhouseBooking => $faker->numberBetween(1500, 12000),
                FinanceCollectionType::Other => $faker->numberBetween(500, 8000),
            };

            $mainMemberId = $type === FinanceCollectionType::Maintenance
                ? $faker->randomElement($mainMemberIds)
                : null;

            $rows[] = [
                'collection_type' => $type->value,
                'main_member_id' => $mainMemberId,
                'amount' => $amount,
                'received_on' => $receivedOn,
                'payment_mode' => $faker->randomElement($paymentModes),
                'reference' => $faker->boolean(55) ? strtoupper($faker->bothify('REF-####-??')) : null,
                'notes' => self::DEMO_COLLECTION_NOTE,
                'recorded_by_user_id' => $actor->id,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $count++;

            if (count($rows) >= 500) {
                FinanceCollection::query()->insert($rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            FinanceCollection::query()->insert($rows);
        }

        return $count;
    }

    private function seedExpenses(User $actor, Carbon $periodStart, Carbon $periodEnd, Carbon $now): int
    {
        $faker = $this->faker();
        $workers = Worker::query()
            ->with(['salaryRates' => fn ($query) => $query->orderByDesc('effective_from')])
            ->where('notes', WorkerSeeder::DEMO_NOTE)
            ->get()
            ->keyBy('id');

        $rows = [];
        $count = 0;

        foreach ($this->monthlyWorkerSalaryDates($periodStart, $periodEnd) as $paidOn) {
            foreach ($workers as $worker) {
                $rate = $this->salaryOnDate($worker, $paidOn);

                if (! $rate) {
                    continue;
                }

                $base = (float) $rate->monthly_salary;
                $adjustment = 0.0;
                $adjustmentNote = null;

                if ($faker->boolean(12)) {
                    $adjustment = (float) $faker->randomElement([500, 300, 200, -200, -500, -1000]);
                    $adjustmentNote = $adjustment > 0 ? 'Festival tip' : 'Leave deduction';
                }

                $rows[] = $this->buildExpenseRow(
                    $worker->worker_type->expenseTag(),
                    $base + $adjustment,
                    $paidOn,
                    $worker->name,
                    $actor->id,
                    $now,
                    $worker->id,
                    $base,
                    $adjustment,
                    $adjustmentNote,
                );

                $count++;

                if (count($rows) >= 500) {
                    FinanceExpense::query()->insert($rows);
                    $rows = [];
                }
            }
        }

        $nonWorkerTags = [
            FinanceExpenseTag::ElectricityBill,
            FinanceExpenseTag::CameraMaintenanceCharge,
            FinanceExpenseTag::SewageCharge,
            FinanceExpenseTag::WaterTankerCharges,
            FinanceExpenseTag::ElectricItemRepairing,
            FinanceExpenseTag::GarbageCollectorPayment,
            FinanceExpenseTag::Others,
        ];

        while ($count < self::TARGET_EXPENSES) {
            $tag = $faker->randomElement($nonWorkerTags);
            $paidOn = $this->randomDateBetween($periodStart, $periodEnd);

            $amount = match ($tag) {
                FinanceExpenseTag::ElectricityBill => $faker->numberBetween(8000, 45000),
                FinanceExpenseTag::CameraMaintenanceCharge => $faker->numberBetween(1500, 6000),
                FinanceExpenseTag::SewageCharge => $faker->numberBetween(3000, 12000),
                FinanceExpenseTag::WaterTankerCharges => $faker->numberBetween(800, 3500),
                FinanceExpenseTag::ElectricItemRepairing => $faker->numberBetween(500, 8000),
                FinanceExpenseTag::GarbageCollectorPayment => $faker->numberBetween(2000, 5000),
                FinanceExpenseTag::Others => $faker->numberBetween(300, 15000),
            };

            $payee = match ($tag) {
                FinanceExpenseTag::ElectricityBill => $faker->randomElement(['Torrent Power', 'UGVCL', 'MGVCL']),
                FinanceExpenseTag::CameraMaintenanceCharge => 'CCTV Solutions',
                FinanceExpenseTag::SewageCharge => 'Municipal sewage vendor',
                FinanceExpenseTag::WaterTankerCharges => $faker->randomElement(['Aqua Tankers', 'Shree Water Supply']),
                FinanceExpenseTag::ElectricItemRepairing => $faker->randomElement(['Patel Electricals', 'Shah Repairs']),
                FinanceExpenseTag::GarbageCollectorPayment => 'City waste contractor',
                FinanceExpenseTag::Others => $faker->company(),
            };

            $rows[] = $this->buildExpenseRow(
                $tag,
                (float) $amount,
                $paidOn,
                $payee,
                $actor->id,
                $now,
            );

            $count++;

            if (count($rows) >= 500) {
                FinanceExpense::query()->insert($rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            FinanceExpense::query()->insert($rows);
        }

        return $count;
    }

    /**
     * @return list<string>
     */
    private function monthlyWorkerSalaryDates(Carbon $periodStart, Carbon $periodEnd): array
    {
        $dates = [];
        $cursor = $periodStart->copy()->startOfMonth();

        while ($cursor->lte($periodEnd)) {
            $paymentDay = min(7, $cursor->daysInMonth);
            $date = $cursor->copy()->day($paymentDay);

            if ($date->betweenIncluded($periodStart, $periodEnd)) {
                $dates[] = $date->toDateString();
            }

            $cursor->addMonth();
        }

        return $dates;
    }

    private function salaryOnDate(Worker $worker, string $paidOn): ?WorkerSalaryRate
    {
        return $worker->salaryRates
            ->first(fn (WorkerSalaryRate $rate) => $rate->effective_from->toDateString() <= $paidOn);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildExpenseRow(
        FinanceExpenseTag $tag,
        float $amount,
        string $paidOn,
        string $payeeName,
        int $actorId,
        Carbon $now,
        ?int $workerId = null,
        ?float $salaryBase = null,
        float $salaryAdjustment = 0,
        ?string $salaryAdjustmentNote = null,
    ): array {
        $faker = $this->faker();

        return [
            'expense_tag' => $tag->value,
            'worker_id' => $workerId,
            'amount' => round($amount, 2),
            'salary_base_amount' => $salaryBase,
            'salary_adjustment' => $workerId ? $salaryAdjustment : 0,
            'salary_adjustment_note' => $salaryAdjustmentNote,
            'paid_on' => $paidOn,
            'payee_name' => $payeeName,
            'reference' => $faker->boolean(40) ? strtoupper($faker->bothify('BILL-####')) : null,
            'notes' => self::DEMO_EXPENSE_NOTE,
            'recorded_by_user_id' => $actorId,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function randomDateBetween(Carbon $start, Carbon $end): string
    {
        $startTs = $start->timestamp;
        $endTs = $end->timestamp;

        return Carbon::createFromTimestamp(random_int($startTs, $endTs))->toDateString();
    }

    private function faker(): Generator
    {
        if ($this->faker === null) {
            $this->faker = FakerFactory::create('en_IN');
            $this->faker->seed(self::FAKER_SEED);
        }

        return $this->faker;
    }
}
