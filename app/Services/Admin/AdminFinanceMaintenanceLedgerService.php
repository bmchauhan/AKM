<?php

namespace App\Services\Admin;

use App\Enums\MaintenanceMonthEntryStatus;
use App\Enums\MembershipRole;
use App\Models\MaintenanceMonthlyEntry;
use App\Models\User;
use App\Support\HouseSearchQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminFinanceMaintenanceLedgerService
{
    public const SESSION_EDITING_ENTRY = 'admin.finance.editing_maintenance_ledger_entry_id';

    public function __construct(
        private readonly AdminFinanceFundSettingService $fundSettings,
        private readonly AdminFinanceMaintenanceChargeService $maintenanceCharges,
        private readonly FinancePaymentReceiptService $receipts,
    ) {}

    /**
     * @param  array{status?: string, house?: string, name?: string}  $filters
     * @return array{
     *     entries: LengthAwarePaginator,
     *     billingMonth: string,
     *     billingMonthLabel: string,
     *     prevMonth: string,
     *     nextMonth: string,
     *     isCurrentMonth: bool,
     *     chargeAmount: ?string,
     *     summary: array{pending: int, due: int, partial: int, paid: int, total_houses: int, expected_total: string, collected_total: string},
     *     filters: array{status: string, house: string, name: string},
     *     hasEntries: bool
     * }
     */
    public function listForScreen(string $month, array $filters = [], int $perPage = 50): array
    {
        $billingMonth = $this->normalizeBillingMonth($month);
        $this->refreshStatusesForMonth($billingMonth);

        $normalizedFilters = [
            'status' => (string) ($filters['status'] ?? ''),
            'house' => trim((string) ($filters['house'] ?? '')),
            'name' => trim((string) ($filters['name'] ?? '')),
        ];

        $query = MaintenanceMonthlyEntry::query()
            ->whereHas('mainMember')
            ->with(['mainMember', 'recordedBy'])
            ->whereDate('billing_month', $billingMonth->toDateString());

        $this->applyEntryFilters($query, $normalizedFilters);

        $entries = $query
            ->clone()
            ->whereHas('mainMember')
            ->join('users', 'users.id', '=', 'maintenance_monthly_entries.main_member_id')
            ->whereNull('users.deleted_at')
            ->orderBy('users.house_type')
            ->orderByRaw('CAST(users.house_number AS UNSIGNED)')
            ->orderBy('users.house_number')
            ->select('maintenance_monthly_entries.*')
            ->paginate($perPage)
            ->withQueryString();

        $entries->getCollection()->transform(fn (MaintenanceMonthlyEntry $entry) => $this->mapListRow($entry));

        $allForMonth = MaintenanceMonthlyEntry::query()
            ->whereHas('mainMember')
            ->whereDate('billing_month', $billingMonth->toDateString())
            ->get();

        $hasActiveFilters = collect($normalizedFilters)->filter()->isNotEmpty();
        $summarySource = $allForMonth;

        if ($hasActiveFilters) {
            $filteredQuery = MaintenanceMonthlyEntry::query()
                ->whereHas('mainMember')
                ->whereDate('billing_month', $billingMonth->toDateString());
            $this->applyEntryFilters($filteredQuery, $normalizedFilters);
            $summarySource = $filteredQuery->get();
        }

        $sampleCharge = $allForMonth->first();

        return [
            'entries' => $entries,
            'billingMonth' => $billingMonth->format('Y-m'),
            'billingMonthLabel' => $billingMonth->format('F Y'),
            'prevMonth' => $billingMonth->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $billingMonth->copy()->addMonth()->format('Y-m'),
            'isCurrentMonth' => $billingMonth->isSameMonth(now()),
            'chargeAmount' => $sampleCharge
                ? $this->fundSettings->formatMoney($sampleCharge->charge_amount)
                : null,
            'summary' => $this->summarizeMonth($summarySource),
            'filters' => $normalizedFilters,
            'hasEntries' => $allForMonth->isNotEmpty(),
            'hasActiveFilters' => $hasActiveFilters,
        ];
    }

    /**
     * @param  array{status?: string, house?: string, name?: string}  $filters
     */
    private function applyEntryFilters(\Illuminate\Database\Eloquent\Builder $query, array $filters): void
    {
        if (filled($filters['status'] ?? '')) {
            $query->where('status', $filters['status']);
        }

        if (filled($filters['house'] ?? '')) {
            $house = trim((string) $filters['house']);
            $query->whereHas('mainMember', function ($memberQuery) use ($house): void {
                HouseSearchQuery::apply($memberQuery, $house);
            });
        }

        if (filled($filters['name'] ?? '')) {
            $name = trim((string) $filters['name']);
            $query->whereHas('mainMember', function ($memberQuery) use ($name): void {
                $memberQuery->where(function ($nameQuery) use ($name): void {
                    $nameQuery->where('first_name', 'like', '%'.$name.'%')
                        ->orWhere('middle_name', 'like', '%'.$name.'%')
                        ->orWhere('last_name', 'like', '%'.$name.'%')
                        ->orWhere('name', 'like', '%'.$name.'%');
                });
            });
        }
    }

    /**
     * @return array{created: int, skipped: int}
     */
    public function generateMonth(Carbon|string $month, User $actor): array
    {
        $billingMonth = $this->normalizeBillingMonth($month);
        $charge = $this->maintenanceCharges->chargeOnDate($billingMonth);

        if (! $charge) {
            throw ValidationException::withMessages([
                'month' => [__('messages.finance_maintenance_charge_not_found')],
            ]);
        }

        $mainMembers = User::query()
            ->where('membership_type', MembershipRole::MainMember->value)
            ->orderBy('house_type')
            ->orderByRaw('CAST(house_number AS UNSIGNED)')
            ->orderBy('house_number')
            ->get();

        $created = 0;
        $skipped = 0;
        $now = now();

        foreach ($mainMembers as $member) {
            $exists = MaintenanceMonthlyEntry::query()
                ->where('main_member_id', $member->id)
                ->whereDate('billing_month', $billingMonth->toDateString())
                ->exists();

            if ($exists) {
                $skipped++;

                continue;
            }

            MaintenanceMonthlyEntry::query()->create([
                'billing_month' => $billingMonth->toDateString(),
                'main_member_id' => $member->id,
                'maintenance_charge_setting_id' => $charge->id,
                'charge_amount' => $charge->monthly_amount,
                'amount_paid' => 0,
                'status' => $this->resolveStatus(0, (float) $charge->monthly_amount, $billingMonth),
                'recorded_by_user_id' => $actor->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $created++;
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    /**
     * @return array{created: int}
     */
    public function syncMissingHouses(Carbon|string $month, User $actor): array
    {
        $result = $this->generateMonth($month, $actor);

        return ['created' => $result['created']];
    }

    public function ensureMonthGenerated(Carbon|string $month, User $actor): void
    {
        $billingMonth = $this->normalizeBillingMonth($month);

        $hasAny = MaintenanceMonthlyEntry::query()
            ->whereDate('billing_month', $billingMonth->toDateString())
            ->exists();

        if (! $hasAny) {
            $this->generateMonth($billingMonth, $actor);
        }
    }

    public function refreshStatusesForMonth(Carbon|string $month): void
    {
        $billingMonth = $this->normalizeBillingMonth($month);

        MaintenanceMonthlyEntry::query()
            ->whereDate('billing_month', $billingMonth->toDateString())
            ->each(function (MaintenanceMonthlyEntry $entry) use ($billingMonth): void {
                $status = $this->resolveStatus(
                    (float) $entry->amount_paid,
                    (float) $entry->charge_amount,
                    $billingMonth,
                );

                if ($entry->status !== $status) {
                    $entry->update(['status' => $status]);
                }
            });
    }

    public function refreshAllOverdueStatuses(): void
    {
        $currentMonth = now()->startOfMonth();

        MaintenanceMonthlyEntry::query()
            ->whereDate('billing_month', '<', $currentMonth->toDateString())
            ->whereIn('status', [
                MaintenanceMonthEntryStatus::Pending->value,
                MaintenanceMonthEntryStatus::Due->value,
                MaintenanceMonthEntryStatus::Partial->value,
            ])
            ->each(function (MaintenanceMonthlyEntry $entry): void {
                $billingMonth = $entry->billing_month->copy()->startOfMonth();
                $status = $this->resolveStatus(
                    (float) $entry->amount_paid,
                    (float) $entry->charge_amount,
                    $billingMonth,
                );

                if ($entry->status !== $status) {
                    $entry->update(['status' => $status]);
                }
            });
    }

    /**
     * @param  array{
     *     amount_paid: float|int|string,
     *     paid_on?: ?string,
     *     payment_mode?: ?string,
     *     reference?: ?string,
     *     notes?: ?string
     * }  $data
     */
    public function updateEntry(MaintenanceMonthlyEntry $entry, User $actor, array $data, bool $suppressReceipt = false): MaintenanceMonthlyEntry
    {
        $previousPaid = (float) $entry->amount_paid;
        $amountPaid = round((float) $data['amount_paid'], 2);
        $charge = (float) $entry->charge_amount;

        if ($amountPaid < 0) {
            throw ValidationException::withMessages([
                'amount_paid' => [__('messages.finance_ledger_amount_invalid')],
            ]);
        }

        if ($amountPaid > $charge + 0.01) {
            throw ValidationException::withMessages([
                'amount_paid' => [__('messages.finance_collection_amount_exceeds_charge')],
            ]);
        }

        $billingMonth = $entry->billing_month->copy()->startOfMonth();
        $status = $this->resolveStatus($amountPaid, $charge, $billingMonth);

        $paidOn = filled($data['paid_on'] ?? null)
            ? $data['paid_on']
            : ($amountPaid > 0 ? now()->toDateString() : null);

        $entry->update([
            'amount_paid' => $amountPaid,
            'status' => $status,
            'paid_on' => $amountPaid > 0 ? $paidOn : null,
            'payment_mode' => $amountPaid > 0 ? ($data['payment_mode'] ?? null) : null,
            'reference' => $data['reference'] ?? null,
            'notes' => $data['notes'] ?? null,
            'recorded_by_user_id' => $actor->id,
        ]);

        $fresh = $entry->fresh(['mainMember', 'recordedBy']);
        $applied = round($amountPaid - $previousPaid, 2);

        if (! $suppressReceipt && $applied > 0.009) {
            $this->receipts->recordMaintenanceSingle($fresh, $actor, $applied, [
                'paid_on' => $paidOn,
                'payment_mode' => $data['payment_mode'] ?? null,
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
        }

        return $fresh;
    }

    public function markPaid(MaintenanceMonthlyEntry $entry, User $actor, array $data = []): MaintenanceMonthlyEntry
    {
        return $this->updateEntry($entry, $actor, [
            'amount_paid' => $entry->charge_amount,
            'paid_on' => $data['paid_on'] ?? now()->toDateString(),
            'payment_mode' => $data['payment_mode'] ?? null,
            'reference' => $data['reference'] ?? null,
            'notes' => $data['notes'] ?? $entry->notes,
        ]);
    }

    public function rememberEditingEntry(MaintenanceMonthlyEntry $entry): void
    {
        session([self::SESSION_EDITING_ENTRY => $entry->id]);
    }

    public function editingEntry(): ?MaintenanceMonthlyEntry
    {
        $entryId = session(self::SESSION_EDITING_ENTRY);

        if (! $entryId) {
            return null;
        }

        return MaintenanceMonthlyEntry::query()
            ->with(['mainMember', 'recordedBy'])
            ->find((int) $entryId);
    }

    public function editingEntryForMonth(string $month): ?MaintenanceMonthlyEntry
    {
        $entry = $this->editingEntry();

        if (! $entry) {
            return null;
        }

        if ($entry->billing_month->format('Y-m') !== $this->normalizeBillingMonth($month)->format('Y-m')) {
            $this->clearEditingEntry();

            return null;
        }

        return $entry;
    }

    public function clearEditingEntry(): void
    {
        session()->forget(self::SESSION_EDITING_ENTRY);
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function statusesForSelect(): array
    {
        return collect(MaintenanceMonthEntryStatus::cases())
            ->map(fn (MaintenanceMonthEntryStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ])
            ->all();
    }

    public function totalMaintenanceCollected(?Carbon $from = null, ?Carbon $to = null): float
    {
        $query = MaintenanceMonthlyEntry::query()->whereHas('mainMember');

        if ($from) {
            $query->whereDate('billing_month', '>=', $from->toDateString());
        }

        if ($to) {
            $query->whereDate('billing_month', '<=', $to->toDateString());
        }

        return (float) $query->sum('amount_paid');
    }

    /**
     * @param  array{house?: string, status?: string}  $filters
     * @return array{
     *     has_searched: bool,
     *     member: ?User,
     *     match_count: int,
     *     match_samples: list<array{house: string, name: string}>,
     *     entries: LengthAwarePaginator,
     *     filters: array{house: string, status: string},
     *     summary: array{
     *         pending: int,
     *         due: int,
     *         partial: int,
     *         paid: int,
     *         months_total: int,
     *         outstanding_total: string,
     *         collected_total: string,
     *         expected_total: string
     *     }
     * }
     */
    public function houseHistoryForScreen(array $filters, bool $hasSearched, int $perPage = 24): array
    {
        $normalizedFilters = [
            'house' => trim((string) ($filters['house'] ?? '')),
            'name' => trim((string) ($filters['name'] ?? '')),
            'member_id' => (int) ($filters['member_id'] ?? 0),
            'status' => trim((string) ($filters['status'] ?? '')),
        ];

        if ($normalizedFilters['status'] === '') {
            $normalizedFilters['status'] = 'outstanding';
        }

        $resolved = $this->resolveMainMemberForHouseLedger($normalizedFilters);
        $member = $hasSearched ? $resolved['member'] : null;
        $emptySummary = [
            'pending' => 0,
            'due' => 0,
            'partial' => 0,
            'paid' => 0,
            'months_total' => 0,
            'outstanding_total' => $this->fundSettings->formatMoney(0),
            'collected_total' => $this->fundSettings->formatMoney(0),
            'expected_total' => $this->fundSettings->formatMoney(0),
        ];

        if (! $member) {
            return [
                'has_searched' => $hasSearched,
                'member' => null,
                'match_count' => $hasSearched ? $resolved['match_count'] : 0,
                'match_samples' => $hasSearched ? $resolved['match_samples'] : [],
                'entries' => MaintenanceMonthlyEntry::query()->whereRaw('1 = 0')->paginate($perPage),
                'filters' => $normalizedFilters,
                'summary' => $emptySummary,
            ];
        }

        $statuses = $this->statusesForHouseFilter($normalizedFilters['status']);

        $query = MaintenanceMonthlyEntry::query()
            ->with(['mainMember', 'recordedBy'])
            ->where('main_member_id', $member->id);

        if ($statuses !== []) {
            $query->whereIn('status', $statuses);
        }

        $allForMember = (clone $query)
            ->orderByDesc('billing_month')
            ->get();

        $entries = $query
            ->orderByDesc('billing_month')
            ->paginate($perPage)
            ->withQueryString();

        $entries->getCollection()->transform(fn (MaintenanceMonthlyEntry $entry) => $this->mapHouseHistoryRow($entry));

        return [
            'has_searched' => true,
            'member' => $member,
            'match_count' => 1,
            'match_samples' => [],
            'entries' => $entries,
            'filters' => $normalizedFilters,
            'summary' => $this->summarizeMemberHistory($allForMember),
            'allocation_entries' => $this->allocationSourceForMember($member),
            'total_outstanding_raw' => $this->totalOutstandingForMember($member),
        ];
    }

    /**
     * @return list<array{
     *     id: int,
     *     billing_month_label: string,
     *     billing_month_key: string,
     *     charge_amount: float,
     *     amount_paid: float,
     *     outstanding: float,
     *     status: string
     * }>
     */
    public function allocationSourceForMember(User $member): array
    {
        return $this->unpaidEntriesForMember($member)
            ->map(fn (MaintenanceMonthlyEntry $entry): array => [
                'id' => $entry->id,
                'billing_month_label' => $entry->billing_month->format('F Y'),
                'billing_month_key' => $entry->billing_month->format('Y-m'),
                'charge_amount' => (float) $entry->charge_amount,
                'amount_paid' => (float) $entry->amount_paid,
                'outstanding' => max(0, round((float) $entry->charge_amount - (float) $entry->amount_paid, 2)),
                'status' => $entry->status instanceof MaintenanceMonthEntryStatus
                    ? $entry->status->value
                    : (string) $entry->status,
            ])
            ->values()
            ->all();
    }

    public function totalOutstandingForMember(User $member): float
    {
        return round(
            collect($this->allocationSourceForMember($member))->sum('outstanding'),
            2,
        );
    }

    /**
     * @return Collection<int, MaintenanceMonthlyEntry>
     */
    public function unpaidEntriesForMember(User $member): Collection
    {
        return MaintenanceMonthlyEntry::query()
            ->where('main_member_id', $member->id)
            ->whereIn('status', [
                MaintenanceMonthEntryStatus::Due->value,
                MaintenanceMonthEntryStatus::Pending->value,
                MaintenanceMonthEntryStatus::Partial->value,
            ])
            ->orderBy('billing_month')
            ->get();
    }

    /**
     * @return array{
     *     payment_amount: float,
     *     total_outstanding: float,
     *     allocated_total: float,
     *     excess_amount: float,
     *     cleared: list<array<string, mixed>>,
     *     partial: ?array<string, mixed>,
     *     remaining: list<array<string, mixed>>
     * }
     */
    public function previewBulkPayment(User $member, float $paymentAmount): array
    {
        $paymentAmount = max(0, round($paymentAmount, 2));
        $entries = $this->unpaidEntriesForMember($member);

        return $this->allocatePaymentAcrossEntries($entries, $paymentAmount);
    }

    /**
     * @param  array{
     *     paid_on?: ?string,
     *     payment_mode?: ?string,
     *     reference?: ?string,
     *     notes?: ?string
     * }  $meta
     * @return array{applied_count: int, preview: array<string, mixed>}
     */
    public function applyBulkPayment(User $member, User $actor, float $paymentAmount, array $meta = []): array
    {
        $paymentAmount = max(0, round($paymentAmount, 2));

        if ($paymentAmount <= 0) {
            throw ValidationException::withMessages([
                'payment_amount' => [__('messages.finance_ledger_bulk_amount_required')],
            ]);
        }

        return DB::transaction(function () use ($member, $actor, $paymentAmount, $meta): array {
            $entries = $this->unpaidEntriesForMember($member);
            $preview = $this->allocatePaymentAcrossEntries($entries, $paymentAmount);
            $appliedCount = 0;

            foreach ($preview['cleared'] as $row) {
                $entry = $entries->firstWhere('id', $row['id']);
                if (! $entry) {
                    continue;
                }

                $this->updateEntry($entry, $actor, [
                    'amount_paid' => $row['new_paid'],
                    'paid_on' => $meta['paid_on'] ?? now()->toDateString(),
                    'payment_mode' => $meta['payment_mode'] ?? null,
                    'reference' => $meta['reference'] ?? null,
                    'notes' => $meta['notes'] ?? null,
                ], suppressReceipt: true);
                $appliedCount++;
            }

            if ($preview['partial']) {
                $entry = $entries->firstWhere('id', $preview['partial']['id']);
                if ($entry) {
                    $this->updateEntry($entry, $actor, [
                        'amount_paid' => $preview['partial']['new_paid'],
                        'paid_on' => $meta['paid_on'] ?? now()->toDateString(),
                        'payment_mode' => $meta['payment_mode'] ?? null,
                        'reference' => $meta['reference'] ?? null,
                        'notes' => $meta['notes'] ?? null,
                    ], suppressReceipt: true);
                    $appliedCount++;
                }
            }

            $this->receipts->recordMaintenanceBulk($member, $actor, $preview, $meta);

            return [
                'applied_count' => $appliedCount,
                'preview' => $preview,
            ];
        });
    }

    /**
     * @param  Collection<int, MaintenanceMonthlyEntry>  $entries
     * @return array{
     *     payment_amount: float,
     *     total_outstanding: float,
     *     allocated_total: float,
     *     excess_amount: float,
     *     cleared: list<array<string, mixed>>,
     *     partial: ?array<string, mixed>,
     *     remaining: list<array<string, mixed>>
     * }
     */
    private function allocatePaymentAcrossEntries(Collection $entries, float $paymentAmount): array
    {
        $remaining = $paymentAmount;
        $totalOutstanding = 0.0;
        $cleared = [];
        $partial = null;
        $remainingMonths = [];
        $allocatedTotal = 0.0;

        foreach ($entries as $entry) {
            $charge = (float) $entry->charge_amount;
            $previousPaid = (float) $entry->amount_paid;
            $outstanding = max(0, round($charge - $previousPaid, 2));

            if ($outstanding <= 0) {
                continue;
            }

            $totalOutstanding += $outstanding;

            if ($remaining <= 0) {
                $remainingMonths[] = $this->mapAllocationRow($entry, 0, $previousPaid, $outstanding);

                continue;
            }

            if ($remaining >= $outstanding - 0.01) {
                $applied = $outstanding;
                $newPaid = round($previousPaid + $applied, 2);
                $remaining = round($remaining - $applied, 2);
                $allocatedTotal = round($allocatedTotal + $applied, 2);
                $cleared[] = $this->mapAllocationRow($entry, $applied, $newPaid, $outstanding, MaintenanceMonthEntryStatus::Paid);

                continue;
            }

            $applied = $remaining;
            $newPaid = round($previousPaid + $applied, 2);
            $allocatedTotal = round($allocatedTotal + $applied, 2);
            $partial = $this->mapAllocationRow($entry, $applied, $newPaid, $outstanding, MaintenanceMonthEntryStatus::Partial);
            $remaining = 0;
        }

        return [
            'payment_amount' => $paymentAmount,
            'total_outstanding' => round($totalOutstanding, 2),
            'allocated_total' => $allocatedTotal,
            'excess_amount' => max(0, round($remaining, 2)),
            'cleared' => $cleared,
            'partial' => $partial,
            'remaining' => $remainingMonths,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapAllocationRow(
        MaintenanceMonthlyEntry $entry,
        float $applied,
        float $newPaid,
        float $outstandingBefore,
        ?MaintenanceMonthEntryStatus $resultStatus = null,
    ): array {
        $status = $resultStatus ?? ($entry->status instanceof MaintenanceMonthEntryStatus
            ? $entry->status
            : MaintenanceMonthEntryStatus::from((string) $entry->status));

        return [
            'id' => $entry->id,
            'billing_month_label' => $entry->billing_month->format('F Y'),
            'billing_month_key' => $entry->billing_month->format('Y-m'),
            'charge_amount' => (float) $entry->charge_amount,
            'charge_formatted' => $this->fundSettings->formatMoney($entry->charge_amount),
            'previous_paid' => (float) $entry->amount_paid,
            'previous_paid_formatted' => $this->fundSettings->formatMoney($entry->amount_paid),
            'applied' => round($applied, 2),
            'applied_formatted' => $this->fundSettings->formatMoney($applied),
            'new_paid' => round($newPaid, 2),
            'new_paid_formatted' => $this->fundSettings->formatMoney($newPaid),
            'outstanding_before' => round($outstandingBefore, 2),
            'outstanding_before_formatted' => $this->fundSettings->formatMoney($outstandingBefore),
            'result_status' => $status->value,
            'result_status_label' => $status->label(),
            'result_status_badge' => $status->badgeClass(),
        ];
    }

    /**
     * @param  array{house?: string, name?: string, member_id?: int}  $filters
     * @return array{
     *     member: ?User,
     *     match_count: int,
     *     match_samples: list<array{id: int, house: string, name: string}>
     * }
     */
    public function resolveMainMemberForHouseLedger(array $filters): array
    {
        $house = trim((string) ($filters['house'] ?? ''));
        $name = trim((string) ($filters['name'] ?? ''));
        $memberId = (int) ($filters['member_id'] ?? 0);

        if ($memberId > 0) {
            $member = User::query()
                ->where('id', $memberId)
                ->where('membership_type', MembershipRole::MainMember->value)
                ->first();

            return [
                'member' => $member,
                'match_count' => $member ? 1 : 0,
                'match_samples' => [],
            ];
        }

        if ($house === '' && $name === '') {
            return ['member' => null, 'match_count' => 0, 'match_samples' => []];
        }

        $query = User::query()
            ->where('membership_type', MembershipRole::MainMember->value);

        if ($house !== '') {
            $query->where(function ($inner) use ($house): void {
                HouseSearchQuery::apply($inner, $house);
            });
        }

        if ($name !== '') {
            $term = '%'.$name.'%';
            $query->where(function ($inner) use ($term): void {
                $inner->where('first_name', 'like', $term)
                    ->orWhere('middle_name', 'like', $term)
                    ->orWhere('last_name', 'like', $term)
                    ->orWhere('name', 'like', $term);
            });
        }

        $matches = $query
            ->orderBy('house_type')
            ->orderByRaw('CAST(house_number AS UNSIGNED)')
            ->orderBy('house_number')
            ->get();

        return [
            'member' => $matches->count() === 1 ? $matches->first() : null,
            'match_count' => $matches->count(),
            'match_samples' => $matches->map(fn (User $user): array => [
                'id' => $user->id,
                'house' => $user->houseLabel() ?? '—',
                'name' => $user->fullName(),
            ])->all(),
        ];
    }

    /**
     * @return array{member: ?User, match_count: int, match_samples: list<array{house: string, name: string}>}
     */
    public function resolveMainMemberByHouse(string $house): array
    {
        $resolved = $this->resolveMainMemberForHouseLedger(['house' => $house]);

        return [
            'member' => $resolved['member'],
            'match_count' => $resolved['match_count'],
            'match_samples' => collect($resolved['match_samples'])
                ->map(fn (array $row): array => [
                    'house' => $row['house'],
                    'name' => $row['name'],
                ])
                ->all(),
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function houseStatusFiltersForSelect(): array
    {
        return [
            ['value' => 'outstanding', 'label' => __('messages.finance_ledger_filter_outstanding')],
            ['value' => 'all', 'label' => __('messages.users_filter_all')],
            ...$this->statusesForSelect(),
        ];
    }

    /**
     * @return list<string>
     */
    private function statusesForHouseFilter(string $filter): array
    {
        if ($filter === 'all') {
            return [];
        }

        if ($filter === 'outstanding') {
            return [
                MaintenanceMonthEntryStatus::Due->value,
                MaintenanceMonthEntryStatus::Pending->value,
                MaintenanceMonthEntryStatus::Partial->value,
            ];
        }

        if (MaintenanceMonthEntryStatus::tryFrom($filter)) {
            return [$filter];
        }

        return [
            MaintenanceMonthEntryStatus::Due->value,
            MaintenanceMonthEntryStatus::Pending->value,
            MaintenanceMonthEntryStatus::Partial->value,
        ];
    }

    public function exportRows(string $month, array $filters = []): Collection
    {
        $billingMonth = $this->normalizeBillingMonth($month);
        $this->refreshStatusesForMonth($billingMonth);

        $query = MaintenanceMonthlyEntry::query()
            ->with('mainMember')
            ->whereDate('billing_month', $billingMonth->toDateString());

        $this->applyEntryFilters($query, [
            'status' => (string) ($filters['status'] ?? ''),
            'house' => trim((string) ($filters['house'] ?? '')),
            'name' => trim((string) ($filters['name'] ?? '')),
        ]);

        return $query
            ->whereHas('mainMember')
            ->join('users', 'users.id', '=', 'maintenance_monthly_entries.main_member_id')
            ->whereNull('users.deleted_at')
            ->orderBy('users.house_type')
            ->orderByRaw('CAST(users.house_number AS UNSIGNED)')
            ->orderBy('users.house_number')
            ->select('maintenance_monthly_entries.*')
            ->get();
    }

    public function normalizeBillingMonth(string|Carbon $month): Carbon
    {
        if ($month instanceof Carbon) {
            return $month->copy()->startOfMonth();
        }

        if (preg_match('/^\d{4}-\d{2}$/', $month)) {
            return Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        }

        return Carbon::parse($month)->startOfMonth();
    }

    private function resolveStatus(float $amountPaid, float $chargeAmount, Carbon $billingMonth): MaintenanceMonthEntryStatus
    {
        if ($amountPaid >= $chargeAmount - 0.01) {
            return MaintenanceMonthEntryStatus::Paid;
        }

        if ($amountPaid > 0) {
            return MaintenanceMonthEntryStatus::Partial;
        }

        if ($billingMonth->lt(now()->startOfMonth())) {
            return MaintenanceMonthEntryStatus::Due;
        }

        return MaintenanceMonthEntryStatus::Pending;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapListRow(MaintenanceMonthlyEntry $entry): array
    {
        $status = $entry->status instanceof MaintenanceMonthEntryStatus
            ? $entry->status
            : MaintenanceMonthEntryStatus::from((string) $entry->status);

        return [
            'id' => $entry->id,
            'house' => $entry->mainMember?->houseLabel() ?? '—',
            'member_name' => $entry->mainMember?->fullName() ?? '—',
            'charge_amount' => $this->fundSettings->formatMoney($entry->charge_amount),
            'amount_paid' => $this->fundSettings->formatMoney($entry->amount_paid),
            'amount_paid_raw' => (float) $entry->amount_paid,
            'charge_amount_raw' => (float) $entry->charge_amount,
            'status' => $status->value,
            'status_label' => $status->label(),
            'status_badge' => $status->badgeClass(),
            'paid_on' => $entry->paid_on?->format('d M Y'),
            'payment_mode' => $entry->payment_mode,
            'notes' => $entry->notes,
        ];
    }

    /**
     * @param  Collection<int, MaintenanceMonthlyEntry>  $entries
     * @return array{pending: int, due: int, partial: int, paid: int, total_houses: int, expected_total: string, collected_total: string}
     */
    private function summarizeMonth(Collection $entries): array
    {
        $counts = [
            'pending' => 0,
            'due' => 0,
            'partial' => 0,
            'paid' => 0,
        ];

        $expected = 0.0;
        $collected = 0.0;

        foreach ($entries as $entry) {
            $status = $entry->status instanceof MaintenanceMonthEntryStatus
                ? $entry->status->value
                : (string) $entry->status;

            if (isset($counts[$status])) {
                $counts[$status]++;
            }

            $expected += (float) $entry->charge_amount;
            $collected += (float) $entry->amount_paid;
        }

        return [
            ...$counts,
            'total_houses' => $entries->count(),
            'expected_total' => $this->fundSettings->formatMoney($expected),
            'collected_total' => $this->fundSettings->formatMoney($collected),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapHouseHistoryRow(MaintenanceMonthlyEntry $entry): array
    {
        return [
            ...$this->mapListRow($entry),
            'billing_month_label' => $entry->billing_month->format('F Y'),
            'billing_month_key' => $entry->billing_month->format('Y-m'),
            'month_ledger_url' => route('admin.finance.maintenance-ledger.index', [
                'month' => $entry->billing_month->format('Y-m'),
            ]),
        ];
    }

    /**
     * @param  Collection<int, MaintenanceMonthlyEntry>  $entries
     * @return array{
     *     pending: int,
     *     due: int,
     *     partial: int,
     *     paid: int,
     *     months_total: int,
     *     outstanding_total: string,
     *     collected_total: string,
     *     expected_total: string
     * }
     */
    private function summarizeMemberHistory(Collection $entries): array
    {
        $counts = [
            'pending' => 0,
            'due' => 0,
            'partial' => 0,
            'paid' => 0,
        ];

        $expected = 0.0;
        $collected = 0.0;
        $outstanding = 0.0;

        foreach ($entries as $entry) {
            $status = $entry->status instanceof MaintenanceMonthEntryStatus
                ? $entry->status->value
                : (string) $entry->status;

            if (isset($counts[$status])) {
                $counts[$status]++;
            }

            $charge = (float) $entry->charge_amount;
            $paid = (float) $entry->amount_paid;
            $expected += $charge;
            $collected += $paid;

            if ($status !== MaintenanceMonthEntryStatus::Paid->value) {
                $outstanding += max(0, $charge - $paid);
            }
        }

        return [
            ...$counts,
            'months_total' => $entries->count(),
            'outstanding_total' => $this->fundSettings->formatMoney($outstanding),
            'collected_total' => $this->fundSettings->formatMoney($collected),
            'expected_total' => $this->fundSettings->formatMoney($expected),
        ];
    }
}
