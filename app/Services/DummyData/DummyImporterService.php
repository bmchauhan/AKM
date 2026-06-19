<?php

namespace App\Services\DummyData;

use App\Enums\FinanceCollectionType;
use App\Enums\FinanceExpenseTag;
use App\Enums\FinancePaymentMode;
use App\Enums\Gender;
use App\Enums\HouseType;
use App\Enums\MembershipRole;
use App\Models\FinanceCollection;
use App\Models\FinanceExpense;
use App\Models\MaintenanceChargeSetting;
use App\Models\MaintenanceMonthlyEntry;
use App\Models\SocietyFundSetting;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerSalaryRate;
use App\Services\Admin\AdminFinanceMaintenanceLedgerService;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DummyImporterService
{
    public const DEMO_PASSWORD = '12345678';

    public const DEMO_EMAIL_DOMAIN = 'dummy.akmyug.local';

    public const DEMO_NOTE = 'Dummy import';

    private ?Generator $faker = null;

    /**
     * @var array<string, bool>
     */
    private array $usedUsernames = [];

    /**
     * @var array<string, bool>
     */
    private array $usedEmails = [];

    /**
     * @var array<string, bool>
     */
    private array $usedMobiles = [];

    private int $mobileCounter = 9100000000;

    public function __construct(
        private readonly AdminFinanceMaintenanceLedgerService $ledger,
    ) {}

    /**
     * @param  callable(string): void|null  $onProgress
     * @return array<string, int>
     */
    public function run(DummyImporterConfig $config, ?callable $onProgress = null): array
    {
        $progress = $onProgress ?? static function (string $message): void {};

        $this->bootstrapFaker();
        $this->loadExistingUniques();

        $actor = $this->resolveActor();

        if (! $actor) {
            throw new RuntimeException('No admin user found. Run database seeders first (SuperAdminSeeder).');
        }

        $housesNeeded = $config->totalHousesNeeded();
        $houseSlots = $this->availableHouseSlots($housesNeeded);

        if (count($houseSlots) < $housesNeeded) {
            throw new RuntimeException(sprintf(
                'Not enough free house slots. Need %d, found %d.',
                $housesNeeded,
                count($houseSlots),
            ));
        }

        $summary = [
            'committee_users' => 0,
            'main_members' => 0,
            'family_members' => 0,
            'maintenance_ledger_entries' => 0,
            'collections' => 0,
            'expenses' => 0,
        ];

        return DB::transaction(function () use ($config, $actor, $houseSlots, $progress, &$summary): array {
            $passwordHash = Hash::make(self::DEMO_PASSWORD);
            $now = now();
            $slotIndex = 0;

            if ($config->userCount > 0) {
                $progress('Creating committee users...');
                $summary['committee_users'] = $this->seedCommitteeUsers(
                    $config->userCount,
                    $houseSlots,
                    $slotIndex,
                    $passwordHash,
                    $now,
                );
                $slotIndex += $config->userCount;
            }

            $mainMembers = collect();

            if ($config->mainMemberCount > 0) {
                $progress('Creating main members...');
                $mainMembers = $this->seedMainMembers(
                    $config->mainMemberCount,
                    $houseSlots,
                    $slotIndex,
                    $passwordHash,
                    $now,
                );
                $summary['main_members'] = $mainMembers->count();
                $slotIndex += $config->mainMemberCount;
            } else {
                $mainMembers = User::query()
                    ->where('membership_type', MembershipRole::MainMember->value)
                    ->where('email', 'like', '%@'.self::DEMO_EMAIL_DOMAIN)
                    ->orderBy('id')
                    ->get();
            }

            if ($config->familyMax > 0 && $mainMembers->isNotEmpty()) {
                $progress('Creating family members...');
                $summary['family_members'] = $this->seedFamilyMembers(
                    $mainMembers,
                    $config->familyMin,
                    $config->familyMax,
                    $passwordHash,
                    $now,
                );
            }

            $this->ensureMaintenanceCharges($actor, $config->financeFrom, $config->maintenanceCharge, $now);
            $this->ensureOpeningBalance($actor, $config->openingBalance, $config->openingBalanceFrom, $now);

            $progress('Generating maintenance ledger entries...');
            $summary['maintenance_ledger_entries'] = $this->seedMaintenanceLedger(
                $actor,
                $config->financeFrom,
                $config->financeTo,
            );

            $progress('Creating finance collections...');
            $summary['collections'] = $this->seedCollections(
                $actor,
                $config->financeFrom,
                $config->financeTo,
                $now,
            );

            if ($config->includeExpenses) {
                $progress('Creating finance expenses...');
                $summary['expenses'] = $this->seedExpenses(
                    $actor,
                    $config->financeFrom,
                    $config->financeTo,
                    $now,
                );
            }

            return $summary;
        });
    }

    private function bootstrapFaker(): void
    {
        $this->faker = FakerFactory::create('en_IN');
        $this->faker->seed(random_int(1, 999_999));
    }

    private function faker(): Generator
    {
        if ($this->faker === null) {
            $this->bootstrapFaker();
        }

        return $this->faker;
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

    private function loadExistingUniques(): void
    {
        foreach (User::query()->pluck('username') as $username) {
            $this->usedUsernames[(string) $username] = true;
        }

        foreach (User::query()->pluck('email') as $email) {
            $this->usedEmails[(string) $email] = true;
        }

        foreach (User::query()->whereNotNull('mobile_number')->pluck('mobile_number') as $mobile) {
            $this->usedMobiles[(string) $mobile] = true;
        }

        $maxMobile = User::query()
            ->whereNotNull('mobile_number')
            ->max('mobile_number');

        if ($maxMobile !== null && (int) $maxMobile >= $this->mobileCounter) {
            $this->mobileCounter = (int) $maxMobile;
        }
    }

    /**
     * @return list<array{house_type: HouseType, house_number: string}>
     */
    private function allHouseSlots(): array
    {
        $slots = [];

        for ($number = 1; $number <= 200; $number++) {
            $slots[] = ['house_type' => HouseType::A, 'house_number' => (string) $number];
        }

        for ($number = 1; $number <= 150; $number++) {
            $slots[] = ['house_type' => HouseType::B, 'house_number' => (string) $number];
        }

        return $slots;
    }

    /**
     * @return list<array{house_type: HouseType, house_number: string}>
     */
    private function availableHouseSlots(int $needed): array
    {
        $occupied = User::query()
            ->whereNotNull('house_type')
            ->whereNotNull('house_number')
            ->get(['house_type', 'house_number'])
            ->mapWithKeys(function (User $user): array {
                $houseType = $user->house_type instanceof HouseType
                    ? $user->house_type->value
                    : (string) $user->house_type;

                return [$houseType.':'.(string) $user->house_number => true];
            });

        $available = [];

        foreach ($this->allHouseSlots() as $slot) {
            $key = $slot['house_type']->value.':'.$slot['house_number'];

            if (! isset($occupied[$key])) {
                $available[] = $slot;

                if (count($available) >= $needed) {
                    break;
                }
            }
        }

        return $available;
    }

    /**
     * @param  list<array{house_type: HouseType, house_number: string}>  $houseSlots
     */
    private function seedCommitteeUsers(
        int $count,
        array $houseSlots,
        int $slotOffset,
        string $passwordHash,
        Carbon $now,
    ): int {
        $rows = [];

        for ($index = 0; $index < $count; $index++) {
            $slot = $houseSlots[$slotOffset + $index];
            $gender = $this->faker()->boolean(40) ? Gender::Female : Gender::Male;
            $names = $this->fakePersonNames($gender);
            $houseType = $slot['house_type']->value;
            $houseNumber = $slot['house_number'];

            $rows[] = $this->buildUserRow([
                ...$names,
                'caste' => $this->fakeCaste(),
                'gender' => $gender->value,
                'house_type' => $houseType,
                'house_number' => $houseNumber,
                'username' => $this->uniqueUsername($names['first_name'], $houseNumber),
                'email' => $this->uniqueEmail($names['first_name'], $names['last_name'], $houseType, $houseNumber, 'cm'.$index),
                'mobile_number' => $this->uniqueMobile(),
                'membership_type' => MembershipRole::MainMember->value,
                'committee_role' => $this->committeeRoleForIndex($index),
            ], $passwordHash, $now);
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            User::query()->insert($chunk);
        }

        return $count;
    }

    /**
     * @param  list<array{house_type: HouseType, house_number: string}>  $houseSlots
     * @return Collection<int, User>
     */
    private function seedMainMembers(
        int $count,
        array $houseSlots,
        int $slotOffset,
        string $passwordHash,
        Carbon $now,
    ): Collection {
        $rows = [];

        for ($index = 0; $index < $count; $index++) {
            $slot = $houseSlots[$slotOffset + $index];
            $gender = $this->faker()->boolean(34) ? Gender::Female : Gender::Male;
            $names = $this->fakePersonNames($gender);
            $houseType = $slot['house_type']->value;
            $houseNumber = $slot['house_number'];

            $rows[] = $this->buildUserRow([
                ...$names,
                'caste' => $this->fakeCaste(),
                'gender' => $gender->value,
                'house_type' => $houseType,
                'house_number' => $houseNumber,
                'username' => $this->uniqueUsername($names['first_name'], $houseNumber),
                'email' => $this->uniqueEmail($names['first_name'], $names['last_name'], $houseType, $houseNumber, 'mm'.$index),
                'mobile_number' => $this->uniqueMobile(),
                'membership_type' => MembershipRole::MainMember->value,
                'committee_role' => null,
            ], $passwordHash, $now);
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            User::query()->insert($chunk);
        }

        return User::query()
            ->where('membership_type', MembershipRole::MainMember->value)
            ->where('email', 'like', '%@'.self::DEMO_EMAIL_DOMAIN)
            ->whereNull('committee_role')
            ->orderByDesc('id')
            ->limit($count)
            ->get();
    }

    /**
     * @param  Collection<int, User>  $mainMembers
     */
    private function seedFamilyMembers(
        Collection $mainMembers,
        int $familyMin,
        int $familyMax,
        string $passwordHash,
        Carbon $now,
    ): int {
        $rows = [];

        foreach ($mainMembers as $mainMember) {
            $familyCount = random_int($familyMin, $familyMax);

            for ($index = 1; $index <= $familyCount; $index++) {
                $gender = $this->faker()->boolean(48) ? Gender::Female : Gender::Male;
                $names = $this->fakePersonNames($gender);
                $houseType = $mainMember->house_type instanceof HouseType
                    ? $mainMember->house_type->value
                    : (string) $mainMember->house_type;

                $rows[] = $this->buildUserRow([
                    'first_name' => $names['first_name'],
                    'middle_name' => $mainMember->first_name,
                    'last_name' => $mainMember->last_name,
                    'caste' => $mainMember->caste,
                    'gender' => $gender->value,
                    'house_type' => $houseType,
                    'house_number' => (string) $mainMember->house_number,
                    'username' => $this->uniqueUsername($names['first_name'], (string) $mainMember->house_number),
                    'email' => $this->uniqueFamilyEmail(
                        $names['first_name'],
                        $mainMember->id,
                        $index,
                        $houseType,
                        (string) $mainMember->house_number,
                    ),
                    'mobile_number' => $this->uniqueMobile(),
                    'membership_type' => MembershipRole::FamilyMember->value,
                    'committee_role' => null,
                    'linked_main_member_id' => $mainMember->id,
                ], $passwordHash, $now);
            }
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            User::query()->insert($chunk);
        }

        return count($rows);
    }

    private function ensureMaintenanceCharges(User $actor, Carbon $financeFrom, float $monthlyAmount, Carbon $now): void
    {
        $effectiveFrom = $financeFrom->copy()->startOfMonth()->toDateString();

        $exists = MaintenanceChargeSetting::query()
            ->where('status', 'active')
            ->whereDate('effective_from', '<=', $effectiveFrom)
            ->where(function ($query) use ($effectiveFrom): void {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $effectiveFrom);
            })
            ->exists();

        if ($exists) {
            return;
        }

        MaintenanceChargeSetting::query()->create([
            'monthly_amount' => round($monthlyAmount, 2),
            'effective_from' => $effectiveFrom,
            'end_date' => null,
            'status' => 'active',
            'notes' => self::DEMO_NOTE,
            'set_by_user_id' => $actor->id,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function ensureOpeningBalance(User $actor, float $openingBalance, Carbon $effectiveFrom, Carbon $now): void
    {
        if (SocietyFundSetting::query()->exists()) {
            return;
        }

        SocietyFundSetting::query()->create([
            'opening_balance' => round($openingBalance, 2),
            'opening_balance_effective_date' => $effectiveFrom->toDateString(),
            'notes' => self::DEMO_NOTE,
            'set_by_user_id' => $actor->id,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function seedMaintenanceLedger(User $actor, Carbon $financeFrom, Carbon $financeTo): int
    {
        $faker = $this->faker();
        $cursor = $financeFrom->copy()->startOfMonth();
        $end = $financeTo->copy()->startOfMonth();
        $currentMonth = now()->startOfMonth();
        $paymentModes = array_column(FinancePaymentMode::cases(), 'value');

        while ($cursor->lte($end)) {
            $this->ledger->generateMonth($cursor, $actor);
            $cursor->addMonth();
        }

        $mainMemberIds = MaintenanceMonthlyEntry::query()
            ->whereDate('billing_month', '>=', $financeFrom->copy()->startOfMonth()->toDateString())
            ->whereDate('billing_month', '<=', $end->toDateString())
            ->distinct()
            ->pluck('main_member_id');

        $mainMembers = User::query()
            ->where('membership_type', MembershipRole::MainMember->value)
            ->where('email', 'like', '%@'.self::DEMO_EMAIL_DOMAIN)
            ->whereIn('id', $mainMemberIds)
            ->orderBy('id')
            ->get();

        foreach ($mainMembers as $member) {
            $monthCursor = $financeFrom->copy()->startOfMonth();

            while ($monthCursor->lte($end)) {
                $isPastMonth = $monthCursor->lt($currentMonth);
                $isCurrentMonth = $monthCursor->equalTo($currentMonth);

                if (! $isPastMonth && ! $isCurrentMonth) {
                    $monthCursor->addMonth();

                    continue;
                }

                $roll = $faker->numberBetween(1, 100);
                $shouldPay = $isPastMonth ? $roll <= 45 : $roll <= 25;

                if (! $shouldPay) {
                    $monthCursor->addMonth();

                    continue;
                }

                $unpaidEntries = $this->ledger->unpaidEntriesForMember($member);

                if ($unpaidEntries->isEmpty()) {
                    $monthCursor->addMonth();

                    continue;
                }

                $oldestCharge = (float) $unpaidEntries->first()->charge_amount;
                $payRoll = $faker->numberBetween(1, 100);

                $amount = match (true) {
                    $payRoll <= 50 => $oldestCharge,
                    $payRoll <= 75 => round($oldestCharge * $faker->randomFloat(2, 0.3, 0.85), 2),
                    default => round($oldestCharge * $faker->numberBetween(1, 3), 2),
                };

                if ($amount < 0.01) {
                    $monthCursor->addMonth();

                    continue;
                }

                $paidOn = $isPastMonth
                    ? $monthCursor->copy()->day(min(28, $faker->numberBetween(5, 28)))->toDateString()
                    : now()->toDateString();

                $this->ledger->applyBulkPayment($member, $actor, $amount, [
                    'paid_on' => $paidOn,
                    'payment_mode' => $faker->randomElement($paymentModes),
                    'notes' => self::DEMO_NOTE,
                ]);

                $monthCursor->addMonth();
            }
        }

        return MaintenanceMonthlyEntry::query()
            ->whereHas('mainMember', fn ($query) => $query->where('email', 'like', '%@'.self::DEMO_EMAIL_DOMAIN))
            ->whereDate('billing_month', '>=', $financeFrom->copy()->startOfMonth()->toDateString())
            ->whereDate('billing_month', '<=', $end->toDateString())
            ->count();
    }

    private function seedCollections(User $actor, Carbon $financeFrom, Carbon $financeTo, Carbon $now): int
    {
        $faker = $this->faker();
        $paymentModes = array_column(FinancePaymentMode::cases(), 'value');
        $months = max(1, $financeFrom->diffInMonths($financeTo) + 1);
        $targetCount = $months * $faker->numberBetween(3, 6);
        $rows = [];
        $count = 0;

        for ($index = 0; $index < $targetCount; $index++) {
            $type = $faker->boolean(60)
                ? FinanceCollectionType::ClubhouseBooking
                : FinanceCollectionType::Other;

            $receivedOn = $this->randomDateBetween($financeFrom, $financeTo);

            $amount = match ($type) {
                FinanceCollectionType::ClubhouseBooking => $faker->numberBetween(1500, 12000),
                FinanceCollectionType::Other => $faker->numberBetween(500, 8000),
            };

            $rows[] = [
                'collection_type' => $type->value,
                'main_member_id' => null,
                'maintenance_charge_setting_id' => null,
                'amount' => $amount,
                'maintenance_base_amount' => null,
                'received_on' => $receivedOn,
                'payment_mode' => $faker->randomElement($paymentModes),
                'reference' => $faker->boolean(55) ? strtoupper($faker->bothify('REF-####-??')) : null,
                'notes' => self::DEMO_NOTE,
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

    private function seedExpenses(User $actor, Carbon $financeFrom, Carbon $financeTo, Carbon $now): int
    {
        $faker = $this->faker();
        $months = max(1, $financeFrom->diffInMonths($financeTo) + 1);
        $targetCount = $months * $faker->numberBetween(4, 8);
        $rows = [];
        $count = 0;

        $workers = Worker::query()
            ->with(['salaryRates' => fn ($query) => $query->orderByDesc('effective_from')])
            ->get()
            ->keyBy('id');

        foreach ($this->monthlyWorkerSalaryDates($financeFrom, $financeTo) as $paidOn) {
            foreach ($workers as $worker) {
                $rate = $this->salaryOnDate($worker, $paidOn);

                if (! $rate) {
                    continue;
                }

                $base = (float) $rate->monthly_salary;

                $rows[] = $this->buildExpenseRow(
                    $worker->worker_type->expenseTag(),
                    $base,
                    $paidOn,
                    $worker->name,
                    $actor->id,
                    $now,
                    $worker->id,
                    $base,
                );

                $count++;

                if (count($rows) >= 500) {
                    FinanceExpense::query()->insert($rows);
                    $rows = [];
                }

                if ($count >= $targetCount) {
                    break 2;
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

        while ($count < $targetCount) {
            $tag = $faker->randomElement($nonWorkerTags);
            $paidOn = $this->randomDateBetween($financeFrom, $financeTo);

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

            $rows[] = $this->buildExpenseRow($tag, (float) $amount, $paidOn, $payee, $actor->id, $now);
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

    private function committeeRoleForIndex(int $index): string
    {
        return match ($index) {
            0 => 'chief_committee_member',
            1 => 'vice_chief_committee_member',
            2 => 'finance_committee_member',
            3 => 'money_collector',
            default => 'committee_member',
        };
    }

    /**
     * @return array{first_name: string, middle_name: string, last_name: string}
     */
    private function fakePersonNames(Gender $gender): array
    {
        $faker = $this->faker();

        $firstName = $gender === Gender::Female
            ? $faker->firstNameFemale()
            : $faker->firstNameMale();

        return [
            'first_name' => $firstName,
            'middle_name' => $faker->firstName(),
            'last_name' => $faker->lastName(),
        ];
    }

    private function fakeCaste(): string
    {
        return $this->faker()->randomElement([
            'Patel', 'Shah', 'Mehta', 'Desai', 'Brahmin', 'Leuva Patel', 'Kadva Patel',
            'Lohana', 'Vaniya', 'Rajput', 'Jain', 'Prajapati', 'Thakkar', 'Sonar', 'Koli',
        ]);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function buildUserRow(array $row, string $passwordHash, Carbon $now): array
    {
        $firstName = (string) $row['first_name'];
        $middleName = filled($row['middle_name'] ?? null) ? (string) $row['middle_name'] : null;
        $lastName = (string) $row['last_name'];
        $fullName = trim(collect([$firstName, $middleName, $lastName])->filter()->implode(' '));

        $committeeRole = $row['committee_role'] ?? null;
        $membershipType = $row['membership_type'] ?? null;

        if ($membershipType === null && filled($committeeRole)) {
            $membershipType = MembershipRole::MainMember->value;
        }

        return [
            'name' => $fullName,
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'caste' => $row['caste'] ?? null,
            'gender' => $row['gender'],
            'house_type' => (string) $row['house_type'],
            'house_number' => (string) $row['house_number'],
            'mobile_number' => (string) $row['mobile_number'],
            'alternate_number' => null,
            'id_proof_path' => null,
            'profile_image_path' => null,
            'email' => (string) $row['email'],
            'email_verified_at' => $now,
            'username' => (string) $row['username'],
            'password' => $passwordHash,
            'membership_type' => $membershipType,
            'committee_role' => $committeeRole,
            'role' => User::syncLegacyRole($membershipType, $committeeRole),
            'linked_main_member_id' => $row['linked_main_member_id'] ?? null,
            'remember_token' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function uniqueUsername(string $firstName, string $houseNumber): string
    {
        $alpha = preg_replace('/[^a-zA-Z]/', '', $firstName) ?: 'USER';
        $prefix = strtoupper(str_pad(substr($alpha, 0, 4), 4, 'X'));

        $base = $prefix.$houseNumber;
        $candidate = $base;
        $suffix = 1;

        while (isset($this->usedUsernames[$candidate])) {
            $candidate = $base.'-'.$suffix;
            $suffix++;
        }

        $this->usedUsernames[$candidate] = true;

        return $candidate;
    }

    private function uniqueEmail(
        string $firstName,
        string $lastName,
        string $houseType,
        string $houseNumber,
        string $prefix,
    ): string {
        $local = $prefix.'.'
            .strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $firstName))
            .'.'
            .strtolower($houseType)
            .$houseNumber;

        $candidate = $local.'@'.self::DEMO_EMAIL_DOMAIN;
        $suffix = 1;

        while (isset($this->usedEmails[$candidate])) {
            $candidate = $local.$suffix.'@'.self::DEMO_EMAIL_DOMAIN;
            $suffix++;
        }

        $this->usedEmails[$candidate] = true;

        return $candidate;
    }

    private function uniqueFamilyEmail(
        string $firstName,
        int $mainMemberId,
        int $familyIndex,
        string $houseType,
        string $houseNumber,
    ): string {
        $local = 'fm.'
            .strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $firstName))
            .'.'
            .$mainMemberId
            .'.'
            .$familyIndex
            .'.'
            .strtolower($houseType)
            .$houseNumber;

        $candidate = $local.'@'.self::DEMO_EMAIL_DOMAIN;
        $suffix = 1;

        while (isset($this->usedEmails[$candidate])) {
            $candidate = $local.'.'.$suffix.'@'.self::DEMO_EMAIL_DOMAIN;
            $suffix++;
        }

        $this->usedEmails[$candidate] = true;

        return $candidate;
    }

    private function uniqueMobile(): string
    {
        do {
            $this->mobileCounter++;
            $candidate = (string) $this->mobileCounter;
        } while (isset($this->usedMobiles[$candidate]));

        $this->usedMobiles[$candidate] = true;

        return $candidate;
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
    ): array {
        $faker = $this->faker();

        return [
            'expense_tag' => $tag->value,
            'worker_id' => $workerId,
            'amount' => round($amount, 2),
            'salary_base_amount' => $salaryBase,
            'salary_adjustment' => 0,
            'salary_adjustment_note' => null,
            'paid_on' => $paidOn,
            'payee_name' => $payeeName,
            'reference' => $faker->boolean(40) ? strtoupper($faker->bothify('BILL-####')) : null,
            'notes' => self::DEMO_NOTE,
            'recorded_by_user_id' => $actorId,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function randomDateBetween(Carbon $start, Carbon $end): string
    {
        $startTs = $start->copy()->startOfDay()->timestamp;
        $endTs = $end->copy()->endOfDay()->timestamp;

        if ($endTs < $startTs) {
            return $start->toDateString();
        }

        return Carbon::createFromTimestamp(random_int($startTs, $endTs))->toDateString();
    }
}
