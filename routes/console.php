<?php

use App\Services\Admin\AdminFinanceMaintenanceLedgerService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('finance:refresh-maintenance-ledger-statuses', function () {
    app(AdminFinanceMaintenanceLedgerService::class)->refreshAllOverdueStatuses();
    $this->info('Maintenance ledger overdue statuses refreshed.');
})->purpose('Mark unpaid past-month maintenance entries as due');

Schedule::command('finance:refresh-maintenance-ledger-statuses')->daily();
