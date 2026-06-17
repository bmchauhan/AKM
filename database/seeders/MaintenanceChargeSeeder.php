<?php

namespace Database\Seeders;

use App\Models\MaintenanceChargeSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class MaintenanceChargeSeeder extends Seeder
{
    public const DEMO_NOTE = 'Demo seed data';

    public function run(): void
    {
        if (MaintenanceChargeSetting::query()->where('notes', self::DEMO_NOTE)->exists()) {
            $this->command?->info('Demo maintenance charges already exist — skipping.');

            return;
        }

        $actor = User::query()
            ->where('email', 'sa@gmail.com')
            ->first()
            ?? User::query()
                ->where('committee_role', 'finance_committee_member')
                ->first();

        if (! $actor) {
            $this->command?->warn('No admin user found — cannot seed maintenance charges.');

            return;
        }

        $now = now();
        $periodStart = now()->subYears(2)->startOfMonth();

        $rates = [
            ['monthly_amount' => 2500, 'effective_from' => $periodStart->toDateString(), 'end_date' => null],
            ['monthly_amount' => 2800, 'effective_from' => $periodStart->copy()->addYear()->toDateString(), 'end_date' => null],
        ];

        foreach ($rates as $index => $rate) {
            if ($index > 0) {
                $previousEnd = Carbon::parse($rate['effective_from'])->subDay()->toDateString();
                MaintenanceChargeSetting::query()
                    ->whereNull('end_date')
                    ->whereDate('effective_from', '<', $rate['effective_from'])
                    ->update(['end_date' => $previousEnd]);
            }

            MaintenanceChargeSetting::query()->create([
                'monthly_amount' => $rate['monthly_amount'],
                'effective_from' => $rate['effective_from'],
                'end_date' => $rate['end_date'],
                'status' => 'active',
                'notes' => self::DEMO_NOTE,
                'set_by_user_id' => $actor->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->command?->info('Seeded demo maintenance charges (₹2,500 then ₹2,800 from year 2).');
    }
}
