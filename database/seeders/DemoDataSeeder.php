<?php

namespace Database\Seeders;

use App\Enums\Gender;
use App\Enums\HouseType;
use App\Enums\MembershipRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    private const DEMO_PASSWORD = '12345678';

    private const DEMO_EMAIL_DOMAIN = 'demo.akmyug.local';

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

    public function run(): void
    {
        $passwordHash = Hash::make(self::DEMO_PASSWORD);
        $now = now();
        $this->loadExistingUniques();

        $hasDemoUsers = User::query()
            ->where('email', 'like', '%@'.self::DEMO_EMAIL_DOMAIN)
            ->exists();

        if (! $hasDemoUsers) {
            $this->seedCommitteeAndMainMembers($passwordHash, $now);
        } else {
            $this->command?->info('Demo committee and main members already exist — skipping.');
        }

        $familyCount = User::query()
            ->where('role', MembershipRole::FamilyMember->value)
            ->where('email', 'like', '%@'.self::DEMO_EMAIL_DOMAIN)
            ->count();

        if ($familyCount === 0) {
            $this->seedFamilyMembers($passwordHash, $now);
        } else {
            $this->command?->info('Demo family members already exist — skipping.');
        }
    }

    private function seedCommitteeAndMainMembers(string $passwordHash, \Illuminate\Support\Carbon $now): void
    {
        $committeeRows = $this->committeeMemberDefinitions();
        $mainMemberRows = $this->mainMemberDefinitions();
        $rows = array_merge($committeeRows, $mainMemberRows);

        foreach (array_chunk($rows, 100) as $chunk) {
            $insert = [];

            foreach ($chunk as $row) {
                $insert[] = $this->buildUserRow($row, $passwordHash, $now);
            }

            User::query()->insert($insert);
        }

        $this->command?->info(sprintf(
            'Seeded %d demo users (%d committee, %d main members). Password for all: %s',
            count($rows),
            count($committeeRows),
            count($mainMemberRows),
            self::DEMO_PASSWORD,
        ));
    }

    private function seedFamilyMembers(string $passwordHash, \Illuminate\Support\Carbon $now): void
    {
        $mainMembers = User::query()
            ->where('role', MembershipRole::MainMember->value)
            ->where('email', 'like', '%@'.self::DEMO_EMAIL_DOMAIN)
            ->orderBy('id')
            ->get();

        if ($mainMembers->isEmpty()) {
            $this->command?->warn('No demo main members found — cannot seed family members.');

            return;
        }

        $rows = [];

        foreach ($mainMembers as $mainMember) {
            $familyCount = random_int(1, 4);

            for ($index = 1; $index <= $familyCount; $index++) {
                $rows[] = $this->familyMemberDefinition($mainMember, $index);
            }
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            $insert = [];

            foreach ($chunk as $row) {
                $insert[] = $this->buildUserRow($row, $passwordHash, $now);
            }

            User::query()->insert($insert);
        }

        $this->command?->info(sprintf(
            'Seeded %d demo family members (1–4 per main member household). Password for all: %s',
            count($rows),
            self::DEMO_PASSWORD,
        ));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function committeeMemberDefinitions(): array
    {
        $definitions = [
            ['first_name' => 'Narendra', 'middle_name' => 'Kumar', 'last_name' => 'Shah', 'role' => 'chief_committee_member', 'gender' => Gender::Male, 'house_type' => HouseType::A, 'house_number' => '901', 'caste' => 'Shah'],
            ['first_name' => 'Pravin', 'middle_name' => 'Ramesh', 'last_name' => 'Patel', 'role' => 'vice_chief_committee_member', 'gender' => Gender::Male, 'house_type' => HouseType::A, 'house_number' => '902', 'caste' => 'Patel'],
            ['first_name' => 'Hitesh', 'middle_name' => 'Jayant', 'last_name' => 'Mehta', 'role' => 'finance_committee_member', 'gender' => Gender::Male, 'house_type' => HouseType::A, 'house_number' => '903', 'caste' => 'Mehta'],
            ['first_name' => 'Kiran', 'middle_name' => 'Mahendra', 'last_name' => 'Desai', 'role' => 'committee_member', 'gender' => Gender::Female, 'house_type' => HouseType::A, 'house_number' => '904', 'caste' => 'Desai'],
            ['first_name' => 'Ashok', 'middle_name' => 'Bhupendra', 'last_name' => 'Gandhi', 'role' => 'committee_member', 'gender' => Gender::Male, 'house_type' => HouseType::A, 'house_number' => '905', 'caste' => 'Gandhi'],
            ['first_name' => 'Meena', 'middle_name' => 'Suresh', 'last_name' => 'Joshi', 'role' => 'committee_member', 'gender' => Gender::Female, 'house_type' => HouseType::A, 'house_number' => '906', 'caste' => 'Joshi'],
            ['first_name' => 'Rakesh', 'middle_name' => 'Vinod', 'last_name' => 'Trivedi', 'role' => 'committee_member', 'gender' => Gender::Male, 'house_type' => HouseType::A, 'house_number' => '907', 'caste' => 'Trivedi'],
            ['first_name' => 'Sunita', 'middle_name' => 'Harshad', 'last_name' => 'Pandya', 'role' => 'committee_member', 'gender' => Gender::Female, 'house_type' => HouseType::A, 'house_number' => '908', 'caste' => 'Pandya'],
            ['first_name' => 'Dilip', 'middle_name' => 'Chandrakant', 'last_name' => 'Modi', 'role' => 'committee_member', 'gender' => Gender::Male, 'house_type' => HouseType::A, 'house_number' => '909', 'caste' => 'Modi'],
            ['first_name' => 'Rekha', 'middle_name' => 'Arvind', 'last_name' => 'Vyas', 'role' => 'committee_member', 'gender' => Gender::Female, 'house_type' => HouseType::A, 'house_number' => '910', 'caste' => 'Vyas'],
            ['first_name' => 'Sanjay', 'middle_name' => 'Mukesh', 'last_name' => 'Thakkar', 'role' => 'committee_member', 'gender' => Gender::Male, 'house_type' => HouseType::A, 'house_number' => '911', 'caste' => 'Thakkar'],
        ];

        return array_map(fn (array $row) => $this->enrichDefinition($row), $definitions);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mainMemberDefinitions(): array
    {
        $firstNamesMale = [
            'Rajesh', 'Amit', 'Vijay', 'Sanjay', 'Ramesh', 'Mahesh', 'Harshad', 'Ketan', 'Bhavin', 'Chirag',
            'Dharmesh', 'Gaurang', 'Hiren', 'Jignesh', 'Kunal', 'Mitesh', 'Nilesh', 'Parth', 'Rahul', 'Sagar',
            'Tushar', 'Umesh', 'Vishal', 'Yogesh', 'Alpesh', 'Bharat', 'Chetan', 'Deepak', 'Eknath', 'Faruk',
        ];

        $firstNamesFemale = [
            'Priya', 'Neha', 'Kavita', 'Anjali', 'Pooja', 'Nisha', 'Divya', 'Hetal', 'Jyoti', 'Kinjal',
            'Lata', 'Manisha', 'Nayana', 'Ojasvi', 'Payal', 'Rina', 'Sejal', 'Trupti', 'Urvashi', 'Vidhi',
            'Asha', 'Bhavna', 'Chandni', 'Disha', 'Esha', 'Falguni', 'Gita', 'Heena', 'Isha', 'Janki',
        ];

        $middleNames = [
            'Kumar', 'Ramesh', 'Suresh', 'Mahendra', 'Jayant', 'Bhupendra', 'Chandrakant', 'Harshad', 'Vinod', 'Arvind',
            'Mukesh', 'Prakash', 'Naresh', 'Dinesh', 'Ashwin', 'Bharat', 'Chiman', 'Dilip', 'Gopal', 'Haresh',
        ];

        $lastNames = [
            'Shah', 'Patel', 'Mehta', 'Desai', 'Gandhi', 'Joshi', 'Trivedi', 'Pandya', 'Modi', 'Vyas',
            'Thakkar', 'Dave', 'Shukla', 'Raval', 'Soni', 'Chauhan', 'Solanki', 'Parmar', 'Rathod', 'Makwana',
        ];

        $castes = ['Patel', 'Shah', 'Mehta', 'Desai', 'Brahmin', 'Leuva Patel', 'Kadva Patel', 'Lohana', 'Vaniya', 'Rajput'];

        $definitions = [];
        $sequence = 0;

        foreach ($this->houseSlots() as $slot) {
            $isFemale = ($sequence % 3) === 1;
            $firstName = $isFemale
                ? $firstNamesFemale[$sequence % count($firstNamesFemale)]
                : $firstNamesMale[$sequence % count($firstNamesMale)];

            $definitions[] = $this->enrichDefinition([
                'first_name' => $firstName,
                'middle_name' => $middleNames[$sequence % count($middleNames)],
                'last_name' => $lastNames[intdiv($sequence, 3) % count($lastNames)],
                'role' => MembershipRole::MainMember->value,
                'gender' => $isFemale ? Gender::Female : Gender::Male,
                'house_type' => $slot['house_type'],
                'house_number' => $slot['house_number'],
                'caste' => $castes[$sequence % count($castes)],
            ]);

            $sequence++;
        }

        return $definitions;
    }

    /**
     * @return list<array{house_type: HouseType, house_number: string}>
     */
    private function houseSlots(): array
    {
        $slots = [];

        for ($number = 1; $number <= 200; $number++) {
            $slots[] = [
                'house_type' => HouseType::A,
                'house_number' => (string) $number,
            ];
        }

        for ($number = 1; $number <= 150; $number++) {
            $slots[] = [
                'house_type' => HouseType::B,
                'house_number' => (string) $number,
            ];
        }

        return $slots;
    }

    /**
     * @return array<string, mixed>
     */
    private function familyMemberDefinition(User $mainMember, int $index): array
    {
        $familyFirstNames = [
            ['first_name' => 'Kiran', 'gender' => Gender::Female],
            ['first_name' => 'Riya', 'gender' => Gender::Female],
            ['first_name' => 'Arjun', 'gender' => Gender::Male],
            ['first_name' => 'Isha', 'gender' => Gender::Female],
            ['first_name' => 'Dev', 'gender' => Gender::Male],
            ['first_name' => 'Sneha', 'gender' => Gender::Female],
            ['first_name' => 'Karan', 'gender' => Gender::Male],
            ['first_name' => 'Mira', 'gender' => Gender::Female],
            ['first_name' => 'Rohan', 'gender' => Gender::Male],
            ['first_name' => 'Tara', 'gender' => Gender::Female],
            ['first_name' => 'Vivaan', 'gender' => Gender::Male],
            ['first_name' => 'Anaya', 'gender' => Gender::Female],
        ];

        $pick = $familyFirstNames[($mainMember->id + $index) % count($familyFirstNames)];

        return $this->enrichFamilyDefinition([
            'first_name' => $pick['first_name'],
            'middle_name' => $mainMember->first_name,
            'last_name' => $mainMember->last_name,
            'role' => MembershipRole::FamilyMember->value,
            'gender' => $pick['gender'],
            'house_type' => $mainMember->house_type?->value ?? (string) $mainMember->house_type,
            'house_number' => (string) $mainMember->house_number,
            'caste' => $mainMember->caste,
            'linked_main_member_id' => $mainMember->id,
            'family_index' => $index,
        ]);
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
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function enrichFamilyDefinition(array $row): array
    {
        $houseType = (string) $row['house_type'];
        $houseNumber = (string) $row['house_number'];
        $firstName = (string) $row['first_name'];
        $mainMemberId = (int) $row['linked_main_member_id'];
        $familyIndex = (int) $row['family_index'];

        $row['username'] = $this->uniqueUsername($firstName, $houseNumber);
        $row['email'] = $this->uniqueFamilyEmail($firstName, $mainMemberId, $familyIndex, $houseType, $houseNumber);
        $row['mobile_number'] = $this->uniqueMobile();

        return $row;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function enrichDefinition(array $row): array
    {
        $houseType = $row['house_type'] instanceof HouseType
            ? $row['house_type']->value
            : (string) $row['house_type'];

        $houseNumber = (string) $row['house_number'];
        $firstName = (string) $row['first_name'];
        $lastName = (string) $row['last_name'];

        $row['house_type'] = $houseType;
        $row['house_number'] = $houseNumber;
        $row['username'] = $this->uniqueUsername($firstName, $houseNumber);
        $row['email'] = $this->uniqueEmail($firstName, $lastName, $houseType, $houseNumber);
        $row['mobile_number'] = $this->uniqueMobile();

        return $row;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function buildUserRow(array $row, string $passwordHash, \Illuminate\Support\Carbon $now): array
    {
        $firstName = (string) $row['first_name'];
        $middleName = filled($row['middle_name'] ?? null) ? (string) $row['middle_name'] : null;
        $lastName = (string) $row['last_name'];

        $fullName = trim(collect([$firstName, $middleName, $lastName])->filter()->implode(' '));

        return [
            'name' => $fullName,
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'caste' => $row['caste'] ?? null,
            'gender' => $row['gender'] instanceof Gender ? $row['gender']->value : (string) $row['gender'],
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
            'role' => (string) $row['role'],
            'linked_main_member_id' => $row['linked_main_member_id'] ?? null,
            'remember_token' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function uniqueUsername(string $firstName, string $houseNumber): string
    {
        $alpha = preg_replace('/[^a-zA-Z]/', '', $firstName) ?: 'USER';
        $prefix = strtoupper(substr($alpha, 0, 4));
        $prefix = str_pad($prefix, 4, 'X');

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

    private function uniqueEmail(string $firstName, string $lastName, string $houseType, string $houseNumber): string
    {
        $local = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $firstName))
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
}
