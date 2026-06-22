<?php

namespace App\Services\Admin;

use App\Enums\FinancePaymentReceiptKind;
use App\Enums\MaintenanceMonthEntryStatus;
use App\Enums\ModulePermissionAction;
use App\Models\FinanceCollection;
use App\Models\FinanceExpense;
use App\Models\FinancePaymentReceipt;
use App\Models\FinancePaymentReceiptLine;
use App\Models\MaintenanceMonthlyEntry;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Support\Facades\DB;

class FinancePaymentReceiptService
{
    public function __construct(
        private readonly AdminFinanceFundSettingService $fundSettings,
    ) {}

    /**
     * @param  array{
     *     paid_on?: ?string,
     *     payment_mode?: ?string,
     *     reference?: ?string,
     *     notes?: ?string
     * }  $meta
     */
    public function recordMaintenanceSingle(
        MaintenanceMonthlyEntry $entry,
        User $actor,
        float $amountApplied,
        array $meta = [],
    ): FinancePaymentReceipt {
        $paidOn = $meta['paid_on'] ?? $entry->paid_on?->toDateString() ?? now()->toDateString();
        $status = $entry->status instanceof MaintenanceMonthEntryStatus
            ? $entry->status
            : MaintenanceMonthEntryStatus::from((string) $entry->status);

        return $this->createReceipt(
            FinancePaymentReceiptKind::Maintenance,
            round($amountApplied, 2),
            [
                'main_member_id' => $entry->main_member_id,
                'paid_on' => $paidOn,
                'payment_mode' => $meta['payment_mode'] ?? $entry->payment_mode?->value ?? $entry->payment_mode,
                'reference' => $meta['reference'] ?? $entry->reference,
                'notes' => $meta['notes'] ?? $entry->notes,
                'recorded_by_user_id' => $actor->id,
            ],
            [[
                'maintenance_monthly_entry_id' => $entry->id,
                'billing_month' => $entry->billing_month,
                'description' => $entry->billing_month->format('F Y'),
                'charge_amount' => $entry->charge_amount,
                'amount_applied' => round($amountApplied, 2),
                'line_status' => $status->value,
                'sort_order' => 0,
            ]],
        );
    }

    /**
     * @param  array{
     *     cleared: list<array<string, mixed>>,
     *     partial: ?array<string, mixed>
     * }  $preview
     * @param  array{
     *     paid_on?: ?string,
     *     payment_mode?: ?string,
     *     reference?: ?string,
     *     notes?: ?string
     * }  $meta
     */
    public function recordMaintenanceBulk(
        User $member,
        User $actor,
        array $preview,
        array $meta = [],
    ): ?FinancePaymentReceipt {
        $lines = [];
        $sort = 0;

        foreach ($preview['cleared'] as $row) {
            if ((float) ($row['applied'] ?? 0) <= 0) {
                continue;
            }

            $lines[] = [
                'maintenance_monthly_entry_id' => $row['id'],
                'billing_month' => $row['billing_month_key'].'-01',
                'description' => $row['billing_month_label'],
                'charge_amount' => $row['charge_amount'],
                'amount_applied' => $row['applied'],
                'line_status' => $row['result_status'],
                'sort_order' => $sort++,
            ];
        }

        if ($preview['partial'] && (float) ($preview['partial']['applied'] ?? 0) > 0) {
            $row = $preview['partial'];
            $lines[] = [
                'maintenance_monthly_entry_id' => $row['id'],
                'billing_month' => $row['billing_month_key'].'-01',
                'description' => $row['billing_month_label'],
                'charge_amount' => $row['charge_amount'],
                'amount_applied' => $row['applied'],
                'line_status' => $row['result_status'],
                'sort_order' => $sort++,
            ];
        }

        if ($lines === []) {
            return null;
        }

        $total = round(collect($lines)->sum(fn (array $line) => (float) $line['amount_applied']), 2);

        return $this->createReceipt(
            FinancePaymentReceiptKind::Maintenance,
            $total,
            [
                'main_member_id' => $member->id,
                'paid_on' => $meta['paid_on'] ?? now()->toDateString(),
                'payment_mode' => $meta['payment_mode'] ?? null,
                'reference' => $meta['reference'] ?? null,
                'notes' => $meta['notes'] ?? null,
                'recorded_by_user_id' => $actor->id,
            ],
            $lines,
        );
    }

    public function recordCollection(FinanceCollection $collection, User $actor): FinancePaymentReceipt
    {
        $type = $collection->collection_type;

        return $this->createReceipt(
            FinancePaymentReceiptKind::Collection,
            (float) $collection->amount,
            [
                'main_member_id' => $collection->main_member_id,
                'finance_collection_id' => $collection->id,
                'paid_on' => $collection->received_on->toDateString(),
                'payment_mode' => $collection->payment_mode?->value ?? $collection->payment_mode,
                'reference' => $collection->reference,
                'notes' => $collection->notes,
                'recorded_by_user_id' => $actor->id,
            ],
            [[
                'description' => $type->label(),
                'amount_applied' => $collection->amount,
                'sort_order' => 0,
            ]],
        );
    }

    public function recordWorkerSalary(FinanceExpense $expense, User $actor): FinancePaymentReceipt
    {
        $lines = [];
        $sort = 0;

        if ($expense->salary_base_amount !== null) {
            $lines[] = [
                'description' => __('messages.workers_salary_base'),
                'amount_applied' => $expense->salary_base_amount,
                'sort_order' => $sort++,
            ];
        }

        if ((float) $expense->salary_adjustment !== 0.0) {
            $lines[] = [
                'description' => __('messages.workers_salary_adjustment'),
                'amount_applied' => $expense->salary_adjustment,
                'sort_order' => $sort++,
            ];
        }

        if ($lines === []) {
            $tag = $expense->expense_tag;
            $lines[] = [
                'description' => $tag->label(),
                'amount_applied' => $expense->amount,
                'sort_order' => 0,
            ];
        }

        return $this->createReceipt(
            FinancePaymentReceiptKind::WorkerSalary,
            (float) $expense->amount,
            [
                'worker_id' => $expense->worker_id,
                'finance_expense_id' => $expense->id,
                'paid_on' => $expense->paid_on->toDateString(),
                'reference' => $expense->reference,
                'notes' => $expense->salary_adjustment_note ?: $expense->notes,
                'recorded_by_user_id' => $actor->id,
            ],
            $lines,
        );
    }

    public function canDownload(FinancePaymentReceipt $receipt, User $actor): bool
    {
        if ($actor->isSuperAdmin()) {
            return true;
        }

        if ($this->actorOwnsReceipt($receipt, $actor)) {
            return true;
        }

        return $this->actorHasFinanceReceiptAdminAccess($receipt, $actor);
    }

    private function actorOwnsReceipt(FinancePaymentReceipt $receipt, User $actor): bool
    {
        if ($receipt->receipt_kind === FinancePaymentReceiptKind::WorkerSalary) {
            return $actor->isSecurityGuard()
                && (int) $actor->workerRecord?->id === (int) $receipt->worker_id;
        }

        return $actor->isResidentMainMember()
            && (int) $receipt->main_member_id === (int) $actor->id;
    }

    private function actorHasFinanceReceiptAdminAccess(FinancePaymentReceipt $receipt, User $actor): bool
    {
        $read = ModulePermissionAction::Read;

        return match ($receipt->receipt_kind) {
            FinancePaymentReceiptKind::WorkerSalary => $actor->canOnAdminModule('finance_expenses', $read),
            FinancePaymentReceiptKind::Maintenance => $actor->canOnAdminModule('finance_maintenance_ledger', $read),
            FinancePaymentReceiptKind::Collection => $actor->canOnAdminModule('finance_collections', $read),
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function pdfViewData(FinancePaymentReceipt $receipt): array
    {
        $receipt->loadMissing(['lines', 'mainMember', 'worker', 'recordedBy']);

        $kind = $receipt->receipt_kind;
        $isMaintenance = $kind === FinancePaymentReceiptKind::Maintenance;
        $isWorkerSalary = $kind === FinancePaymentReceiptKind::WorkerSalary;

        $payeeName = $isWorkerSalary
            ? ($receipt->worker?->name ?? '—')
            : ($receipt->mainMember?->fullName() ?? '—');

        $payeeHouse = $isWorkerSalary
            ? null
            : $receipt->mainMember?->houseLabel();

        $lines = $receipt->lines->map(function (FinancePaymentReceiptLine $line) use ($isMaintenance, $isWorkerSalary) {
            $statusLabel = null;
            if ($isMaintenance && $line->line_status) {
                $statusLabel = MaintenanceMonthEntryStatus::tryFrom($line->line_status)?->label();
            }

            return [
                'period' => $line->billing_month?->format('F Y') ?? $line->description ?? '—',
                'description' => $line->description ?? '—',
                'charge' => $line->charge_amount !== null
                    ? $this->fundSettings->formatMoney($line->charge_amount)
                    : null,
                'applied' => $this->fundSettings->formatMoney($line->amount_applied),
                'status' => $statusLabel,
            ];
        })->all();

        return [
            'receipt' => $receipt,
            'receipt_number' => $receipt->receipt_number,
            'receipt_title' => $isWorkerSalary
                ? __('messages.finance_receipt_title_salary')
                : __('messages.finance_receipt_title_payment'),
            'receipt_kind_label' => $kind->label(),
            'paid_on' => $receipt->paid_on->format('d M Y'),
            'payment_mode' => $receipt->payment_mode?->label(),
            'reference' => $receipt->reference,
            'notes' => $receipt->notes,
            'payee_name' => $payeeName,
            'payee_house' => $payeeHouse,
            'payee_label' => $isWorkerSalary
                ? __('messages.workers_name')
                : __('messages.finance_receipt_member'),
            'total_amount' => $this->fundSettings->formatMoney($receipt->total_amount),
            'lines' => $lines,
            'show_charge_column' => $isMaintenance,
            'show_status_column' => $isMaintenance,
            'line_heading' => $isMaintenance
                ? __('messages.finance_receipt_maintenance_breakdown')
                : ($isWorkerSalary
                    ? __('messages.finance_receipt_salary_breakdown')
                    : __('messages.finance_receipt_payment_details')),
            'generated_at' => now()->format('d M Y, h:i A'),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    private function createReceipt(
        FinancePaymentReceiptKind $kind,
        float $totalAmount,
        array $attributes,
        array $lines,
    ): FinancePaymentReceipt {
        return DB::transaction(function () use ($kind, $totalAmount, $attributes, $lines): FinancePaymentReceipt {
            $receipt = FinancePaymentReceipt::query()->create([
                'receipt_number' => $this->nextReceiptNumber(),
                'receipt_kind' => $kind->value,
                'total_amount' => round($totalAmount, 2),
                ...$attributes,
            ]);

            foreach ($lines as $line) {
                FinancePaymentReceiptLine::query()->create([
                    'finance_payment_receipt_id' => $receipt->id,
                    ...$line,
                ]);
            }

            return $receipt->load('lines');
        });
    }

    private function nextReceiptNumber(): string
    {
        $year = now()->format('Y');
        $latest = FinancePaymentReceipt::query()
            ->where('receipt_number', 'like', "AKM/RCP/{$year}/%")
            ->orderByDesc('id')
            ->value('receipt_number');

        $sequence = 1;
        if (is_string($latest) && preg_match('/(\d+)$/', $latest, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return sprintf('AKM/RCP/%s/%05d', $year, $sequence);
    }
}
