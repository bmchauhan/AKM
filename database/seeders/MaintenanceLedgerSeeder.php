<?php

namespace Database\Seeders;

use App\Enums\MaintenanceMonthEntryStatus;
use App\Models\MaintenanceMonthlyEntry;
use App\Models\User;
use App\Services\Admin\AdminFinanceMaintenanceLedgerService;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class MaintenanceLedgerSeeder extends Seeder
{
    public function run(): void
    {
        if (MaintenanceMonthlyEntry::query()->exists()) {
            $this->command?->info('Maintenance ledger entries already exist — skipping.');

            return;
        }

        $actor = User::query()
            ->where('email', 'sa@gmail.com')
            ->first()
            ?? User::query()->where('committee_role', 'finance_committee_member')->first();

        if (! $actor) {
            $this->command?->warn('No admin user found — cannot seed maintenance ledger.');

            return;
        }

        $ledger = app(AdminFinanceMaintenanceLedgerService::class);
        $faker = FakerFactory::create('en_IN');
        $faker->seed(20260612);

        $start = now()->subMonths(5)->startOfMonth();
        $end = now()->startOfMonth();
        $cursor = $start->copy();
        $total = 0;

        while ($cursor->lte($end)) {
            $ledger->generateMonth($cursor, $actor);

            $entries = MaintenanceMonthlyEntry::query()
                ->whereDate('billing_month', $cursor->toDateString())
                ->get();

            foreach ($entries as $entry) {
                $roll = $faker->numberBetween(1, 100);
                $charge = (float) $entry->charge_amount;

                if ($cursor->lt(now()->startOfMonth())) {
                    if ($roll <= 55) {
                        $entry->update([
                            'amount_paid' => $charge,
                            'status' => MaintenanceMonthEntryStatus::Paid,
                            'paid_on' => $cursor->copy()->day(min(28, $faker->numberBetween(1, 28))),
                        ]);
                    } elseif ($roll <= 70) {
                        $partial = round($charge * $faker->randomFloat(2, 0.3, 0.85), 2);
                        $entry->update([
                            'amount_paid' => $partial,
                            'status' => MaintenanceMonthEntryStatus::Partial,
                            'paid_on' => $cursor->copy()->day(min(28, $faker->numberBetween(1, 28))),
                        ]);
                    } else {
                        $entry->update(['status' => MaintenanceMonthEntryStatus::Due]);
                    }
                } else {
                    if ($roll <= 25) {
                        $entry->update([
                            'amount_paid' => $charge,
                            'status' => MaintenanceMonthEntryStatus::Paid,
                            'paid_on' => now()->toDateString(),
                        ]);
                    } elseif ($roll <= 35) {
                        $partial = round($charge * $faker->randomFloat(2, 0.4, 0.8), 2);
                        $entry->update([
                            'amount_paid' => $partial,
                            'status' => MaintenanceMonthEntryStatus::Partial,
                            'paid_on' => now()->toDateString(),
                        ]);
                    }
                }

                $total++;
            }

            $cursor->addMonth();
        }

        $this->command?->info(sprintf('Seeded %d maintenance ledger entries across 6 months.', $total));
    }
}
