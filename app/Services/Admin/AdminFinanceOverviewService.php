<?php

namespace App\Services\Admin;

use App\Enums\FinanceCollectionType;
use App\Enums\FinanceExpenseTag;
use App\Models\FinanceCollection;
use App\Models\FinanceExpense;
use App\Models\MaintenanceMonthlyEntry;
use Illuminate\Support\Carbon;

class AdminFinanceOverviewService
{
    public function __construct(
        private readonly AdminFinanceFundSettingService $fundSettings,
        private readonly AdminFinanceMaintenanceLedgerService $ledger,
    ) {}

    /**
     * @return array{
     *     is_configured: bool,
     *     formatted_opening_balance: string,
     *     formatted_effective_date: ?string,
     *     fund_stats: list<array{key: string, label: string, value: string, hint: ?string}>,
     *     recent_collections: list<array<string, mixed>>,
     *     recent_expenses: list<array<string, mixed>>
     * }
     */
    public function screenData(): array
    {
        $setting = $this->fundSettings->current();
        $effectiveDate = $setting?->opening_balance_effective_date;
        $openingBalance = (float) ($setting?->opening_balance ?? 0);

        $collectionsBase = FinanceCollection::query();
        $expensesBase = FinanceExpense::query();

        if ($effectiveDate) {
            $collectionsBase->whereDate('received_on', '>=', $effectiveDate);
            $expensesBase->whereDate('paid_on', '>=', $effectiveDate);
        }

        $totalCollections = (float) (clone $collectionsBase)
            ->where('collection_type', '!=', FinanceCollectionType::Maintenance->value)
            ->sum('amount');
        $totalCollections += $this->ledger->totalMaintenanceCollected(
            $effectiveDate ? Carbon::parse($effectiveDate)->startOfMonth() : null,
        );
        $totalExpenses = (float) (clone $expensesBase)->sum('amount');
        $fundBalance = $openingBalance + $totalCollections - $totalExpenses;

        $previousMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $previousMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $previousMonthExpenses = (float) FinanceExpense::query()
            ->when($effectiveDate, fn ($query) => $query->whereDate('paid_on', '>=', $effectiveDate))
            ->whereBetween('paid_on', [$previousMonthStart, $previousMonthEnd])
            ->sum('amount');

        $maintenanceThisYear = (float) MaintenanceMonthlyEntry::query()
            ->whereYear('billing_month', Carbon::now()->year)
            ->sum('amount_paid');

        $collectionsThisMonth = (float) MaintenanceMonthlyEntry::query()
            ->whereYear('billing_month', Carbon::now()->year)
            ->whereMonth('billing_month', Carbon::now()->month)
            ->sum('amount_paid');
        $collectionsThisMonth += (float) FinanceCollection::query()
            ->where('collection_type', '!=', FinanceCollectionType::Maintenance->value)
            ->whereYear('received_on', Carbon::now()->year)
            ->whereMonth('received_on', Carbon::now()->month)
            ->when($effectiveDate, fn ($query) => $query->whereDate('received_on', '>=', $effectiveDate))
            ->sum('amount');

        return [
            'is_configured' => $setting !== null,
            'formatted_opening_balance' => $this->fundSettings->formatMoney($openingBalance),
            'formatted_effective_date' => $effectiveDate?->format('d M Y'),
            'fund_stats' => [
                [
                    'key' => 'fund_balance',
                    'label' => __('messages.finance_stat_fund_balance'),
                    'value' => $this->fundSettings->formatMoney($fundBalance),
                    'hint' => __('messages.finance_stat_fund_balance_hint'),
                ],
                [
                    'key' => 'expense_total',
                    'label' => __('messages.finance_stat_expense_total'),
                    'value' => $this->fundSettings->formatMoney($totalExpenses),
                    'hint' => __('messages.finance_stat_expense_total_hint'),
                ],
                [
                    'key' => 'expense_previous_month',
                    'label' => __('messages.finance_stat_expense_previous_month'),
                    'value' => $this->fundSettings->formatMoney($previousMonthExpenses),
                    'hint' => __('messages.finance_stat_expense_previous_month_hint', [
                        'month' => $previousMonthStart->format('F Y'),
                    ]),
                ],
                [
                    'key' => 'maintenance_this_year',
                    'label' => __('messages.finance_stat_maintenance_year'),
                    'value' => $this->fundSettings->formatMoney($maintenanceThisYear),
                    'hint' => __('messages.finance_stat_maintenance_year_hint', [
                        'year' => (string) Carbon::now()->year,
                    ]),
                ],
                [
                    'key' => 'collections_this_month',
                    'label' => __('messages.finance_stat_collections_month'),
                    'value' => $this->fundSettings->formatMoney($collectionsThisMonth),
                    'hint' => __('messages.finance_stat_collections_month_hint', [
                        'month' => Carbon::now()->format('F Y'),
                    ]),
                ],
            ],
            'recent_collections' => $this->recentCollections(),
            'recent_expenses' => $this->recentExpenses(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function recentCollections(): array
    {
        $ledgerRows = MaintenanceMonthlyEntry::query()
            ->with(['mainMember'])
            ->where('amount_paid', '>', 0)
            ->latest('paid_on')
            ->latest('id')
            ->limit(4)
            ->get()
            ->map(fn (MaintenanceMonthlyEntry $item): array => [
                'date' => $item->paid_on?->format('d M Y') ?? $item->billing_month->format('d M Y'),
                'type_label' => __('messages.finance_collection_maintenance'),
                'house' => $item->mainMember?->houseLabel() ?? '—',
                'main_member' => $item->mainMember?->fullName(),
                'amount' => $this->fundSettings->formatMoney($item->amount_paid),
            ]);

        $otherRows = FinanceCollection::query()
            ->with(['mainMember', 'recordedBy'])
            ->where('collection_type', '!=', FinanceCollectionType::Maintenance->value)
            ->latest('received_on')
            ->latest('id')
            ->limit(4)
            ->get()
            ->map(function (FinanceCollection $item): array {
                $type = $item->collection_type instanceof FinanceCollectionType
                    ? $item->collection_type
                    : FinanceCollectionType::from((string) $item->collection_type);

                return [
                    'date' => $item->received_on->format('d M Y'),
                    'type_label' => $type->label(),
                    'house' => $item->mainMember?->houseLabel() ?? '—',
                    'main_member' => $item->mainMember?->fullName(),
                    'amount' => $this->fundSettings->formatMoney($item->amount),
                ];
            });

        return $ledgerRows->concat($otherRows)->sortByDesc('date')->take(8)->values()->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function recentExpenses(): array
    {
        return FinanceExpense::query()
            ->with('recordedBy')
            ->latest('paid_on')
            ->latest('id')
            ->limit(8)
            ->get()
            ->map(function (FinanceExpense $item): array {
                $tag = $item->expense_tag instanceof FinanceExpenseTag
                    ? $item->expense_tag
                    : FinanceExpenseTag::from((string) $item->expense_tag);

                return [
                    'date' => $item->paid_on->format('d M Y'),
                    'tag_label' => $tag->label(),
                    'payee_name' => $item->payee_name ?? '—',
                    'amount' => $this->fundSettings->formatMoney($item->amount),
                ];
            })
            ->all();
    }
}
