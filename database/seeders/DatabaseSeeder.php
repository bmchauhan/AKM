<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            ModuleSeeder::class,
            SubModuleSeeder::class,
            SubModulePermissionSeeder::class,
            CommitteeRoleSeeder::class,
            SuperAdminSeeder::class,

            // Legacy demo seeders — disabled (no dummy users or sample data on fresh install).
            // DemoDataSeeder::class,
            // WorkerSeeder::class,
            // MaintenanceChargeSeeder::class,
            // MaintenanceLedgerSeeder::class,
            // DemoFinanceSeeder::class,
            // UsefulDirectorySeeder::class,
        ]);
    }
}
