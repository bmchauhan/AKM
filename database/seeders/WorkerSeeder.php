<?php

namespace Database\Seeders;

use App\Enums\WorkerType;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerSalaryRate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class WorkerSeeder extends Seeder
{
    public const DEMO_NOTE = 'Demo seed data';

    public function run(): void
    {
        if (Worker::query()->where('notes', self::DEMO_NOTE)->exists()) {
            $this->command?->info('Demo workers already exist — skipping.');

            return;
        }

        $actor = User::query()
            ->where('email', 'sa@gmail.com')
            ->first()
            ?? User::query()
                ->where('committee_role', 'finance_committee_member')
                ->first();

        if (! $actor) {
            $this->command?->warn('No admin user found — cannot seed workers.');

            return;
        }

        $effectiveFrom = now()->subYears(2)->startOfMonth()->toDateString();
        $joinedOn = $effectiveFrom;
        $now = now();

        $definitions = [
            [
                'worker_type' => WorkerType::Sweeper,
                'name' => 'Ramesh Solanki',
                'mobile_number' => '9876501001',
                'address' => 'Staff quarter, Block A — society sweeper',
                'monthly_salary' => 10000,
            ],
            [
                'worker_type' => WorkerType::SecurityGuard,
                'name' => 'Vijay Singh',
                'mobile_number' => '9876502001',
                'address' => 'Main gate — day shift',
                'monthly_salary' => 14000,
            ],
            [
                'worker_type' => WorkerType::SecurityGuard,
                'name' => 'Sunil Thakur',
                'mobile_number' => '9876502002',
                'address' => 'Main gate — night shift',
                'monthly_salary' => 14000,
            ],
            [
                'worker_type' => WorkerType::SecurityGuard,
                'name' => 'Mahesh Parmar',
                'mobile_number' => '9876502003',
                'address' => 'Rear gate — full day',
                'monthly_salary' => 14000,
            ],
            [
                'worker_type' => WorkerType::Gardener,
                'name' => 'Ashok Prajapati',
                'mobile_number' => '9876503001',
                'address' => 'Garden & lawn maintenance',
                'monthly_salary' => 10000,
            ],
        ];

        foreach ($definitions as $definition) {
            $worker = Worker::query()->create([
                'worker_type' => $definition['worker_type']->value,
                'name' => $definition['name'],
                'mobile_number' => $definition['mobile_number'],
                'address' => $definition['address'],
                'joined_on' => $joinedOn,
                'is_active' => true,
                'notes' => self::DEMO_NOTE,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            WorkerSalaryRate::query()->create([
                'worker_id' => $worker->id,
                'monthly_salary' => $definition['monthly_salary'],
                'effective_from' => $effectiveFrom,
                'notes' => 'Initial demo salary',
                'set_by_user_id' => $actor->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->command?->info(sprintf(
            'Seeded %d demo workers (1 sweeper @ ₹10,000, 3 security guards @ ₹14,000, 1 gardener @ ₹10,000). Effective from %s.',
            count($definitions),
            Carbon::parse($effectiveFrom)->format('d M Y'),
        ));
    }
}
