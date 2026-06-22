<?php

namespace App\Console\Commands;

use App\Services\DummyData\DummyImporterConfig;
use App\Services\DummyData\DummyImporterService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use Throwable;

class DummyImportCommand extends Command
{
    protected $signature = 'dummy:import
                            {--users= : Number of committee users to create}
                            {--main-members= : Number of main members to create}
                            {--family-range= : Family members per main member, e.g. 1-3}
                            {--finance-from= : Finance entries start date (Y-m-d)}
                            {--finance-to= : Finance entries end date (Y-m-d)}
                            {--maintenance-charge= : Monthly maintenance charge amount}
                            {--opening-balance= : Society fund opening balance amount}
                            {--opening-balance-from= : Opening balance effective date (Y-m-d)}
                            {--workers= : Number of workers to create}
                            {--worker-salary-range= : Worker monthly salary range, e.g. 10000-14000}
                            {--no-expenses : Skip expense records}
                            {--no-worker-payments : Skip monthly worker salary payment records}
                            {--force : Skip confirmation prompt}';

    protected $description = 'Interactively import dummy members and finance records for testing';

    public function handle(DummyImporterService $importer): int
    {
        $this->components->info('Dummy data importer — अक्षरयुग FamilyGroup');

        if (! $this->option('force') && ! $this->confirm('This will insert dummy records into the database. Continue?', true)) {
            $this->components->warn('Import cancelled.');

            return self::SUCCESS;
        }

        try {
            $config = $this->resolveConfig();
        } catch (InvalidArgumentException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->components->twoColumnDetail('Committee users', (string) $config->userCount);
        $this->components->twoColumnDetail('Main members', (string) $config->mainMemberCount);
        $this->components->twoColumnDetail('Family members / household', $config->familyMin.'–'.$config->familyMax);
        $this->components->twoColumnDetail('Finance from', $config->financeFrom->toDateString());
        $this->components->twoColumnDetail('Finance to', $config->financeTo->toDateString());
        $this->components->twoColumnDetail('Maintenance charge', '₹'.number_format($config->maintenanceCharge, 2));
        $this->components->twoColumnDetail('Opening balance', '₹'.number_format($config->openingBalance, 2));
        $this->components->twoColumnDetail('Opening balance from', $config->openingBalanceFrom->toDateString());
        $this->components->twoColumnDetail('Workers', (string) $config->workerCount);
        $this->components->twoColumnDetail('Worker salary / month', '₹'.number_format($config->workerSalaryMin, 0)
            .($config->workerSalaryMin !== $config->workerSalaryMax
                ? '–₹'.number_format($config->workerSalaryMax, 0)
                : ''));
        $this->components->twoColumnDetail('Include expenses', $config->includeExpenses ? 'Yes' : 'No');
        $this->components->twoColumnDetail('Worker salary payments', $config->includeWorkerPayments ? 'Yes' : 'No');
        $this->newLine();

        try {
            $summary = $importer->run($config, function (string $message): void {
                $this->line("  → {$message}");
            });
        } catch (Throwable $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->components->info('Dummy import completed.');

        $this->table(
            ['Record type', 'Count'],
            collect($summary)->map(fn (int $count, string $type) => [
                str_replace('_', ' ', ucfirst($type)),
                $count,
            ])->values()->all(),
        );

        $this->newLine();
        $this->components->twoColumnDetail(
            'Dummy login password',
            DummyImporterService::DEMO_PASSWORD,
        );
        $this->components->twoColumnDetail(
            'Dummy email domain',
            '@'.DummyImporterService::DEMO_EMAIL_DOMAIN,
        );

        return self::SUCCESS;
    }

    private function resolveConfig(): DummyImporterConfig
    {
        $userCount = $this->resolveIntOption('users', 'How many committee users?', 10);
        $mainMemberCount = $this->resolveIntOption('main-members', 'How many main members?', 30);

        $familyRangeInput = $this->option('family-range')
            ?? $this->ask('Family members per main member (range, e.g. 1-3)?', '1-3');

        [$familyMin, $familyMax] = DummyImporterConfig::parseFamilyRange((string) $familyRangeInput);

        $financeFromInput = $this->option('finance-from')
            ?? $this->ask('Finance entry from date (Y-m-d)?', now()->subYear()->toDateString());

        $financeToInput = $this->option('finance-to')
            ?? $this->ask('Finance entry to date (Y-m-d)?', now()->toDateString());

        $financeFrom = $this->parseDate((string) $financeFromInput, 'Finance from date');
        $financeTo = $this->parseDate((string) $financeToInput, 'Finance to date');

        $maintenanceCharge = $this->resolveAmountOption(
            'maintenance-charge',
            'Monthly maintenance charge amount (₹)?',
            2500,
        );

        $openingBalance = $this->resolveBalanceOption(
            'opening-balance',
            'Society opening balance amount (₹)?',
            500000,
        );

        $openingBalanceFromInput = $this->option('opening-balance-from')
            ?? $this->ask(
                'Opening balance effective from date (Y-m-d)?',
                $financeFrom->copy()->startOfMonth()->toDateString(),
            );

        $openingBalanceFrom = $this->parseDate(
            (string) $openingBalanceFromInput,
            'Opening balance effective date',
        );

        $includeExpenses = ! $this->option('no-expenses');

        if (! $this->option('no-expenses') && $this->option('finance-from') === null && $this->option('finance-to') === null) {
            $includeExpenses = $this->confirm('Include finance expense records?', true);
        }

        $workerCount = $this->resolveIntOption('workers', 'How many workers?', 5);

        $workerSalaryRangeInput = $this->option('worker-salary-range')
            ?? $this->ask('Worker monthly salary range (e.g. 10000-14000)?', '10000-14000');

        [$workerSalaryMin, $workerSalaryMax] = DummyImporterConfig::parseSalaryRange((string) $workerSalaryRangeInput);

        $includeWorkerPayments = ! $this->option('no-worker-payments');

        if (
            $includeWorkerPayments
            && ! $this->option('no-worker-payments')
            && $this->option('finance-from') === null
            && $this->option('finance-to') === null
        ) {
            $includeWorkerPayments = $this->confirm('Include monthly worker salary payments?', true);
        }

        return new DummyImporterConfig(
            userCount: $userCount,
            mainMemberCount: $mainMemberCount,
            familyMin: $familyMin,
            familyMax: $familyMax,
            financeFrom: $financeFrom,
            financeTo: $financeTo,
            maintenanceCharge: $maintenanceCharge,
            openingBalance: $openingBalance,
            openingBalanceFrom: $openingBalanceFrom,
            includeExpenses: $includeExpenses,
            workerCount: $workerCount,
            workerSalaryMin: $workerSalaryMin,
            workerSalaryMax: $workerSalaryMax,
            includeWorkerPayments: $includeWorkerPayments,
        );
    }

    private function resolveBalanceOption(string $option, string $question, float $default): float
    {
        $value = $this->option($option);

        if ($value !== null && $value !== '') {
            return max(0, (float) $value);
        }

        return max(0, (float) $this->ask($question, (string) $default));
    }

    private function resolveAmountOption(string $option, string $question, float $default): float
    {
        $value = $this->option($option);

        if ($value !== null && $value !== '') {
            return max(0.01, (float) $value);
        }

        return max(0.01, (float) $this->ask($question, (string) $default));
    }

    private function resolveIntOption(string $option, string $question, int $default): int
    {
        $value = $this->option($option);

        if ($value !== null && $value !== '') {
            return max(0, (int) $value);
        }

        return max(0, (int) $this->ask($question, (string) $default));
    }

    private function parseDate(string $value, string $label): Carbon
    {
        try {
            return Carbon::parse($value)->startOfDay();
        } catch (Throwable) {
            throw new InvalidArgumentException($label.' is not a valid date. Use Y-m-d format.');
        }
    }
}
