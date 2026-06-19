<?php

namespace App\Services\Admin;

use App\Enums\FinanceCollectionType;
use App\Enums\FinanceExpenseTag;
use App\Models\FinanceCollection;
use App\Models\FinanceExpense;
use App\Models\MaintenanceMonthlyEntry;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AdminFinanceOverviewService
{
    private const CHART_COLORS = [
        '#AB1E23',
        '#E6C280',
        '#080D21',
        '#E5989B',
        '#0F141E',
        '#E6EBF4',
    ];

    public function __construct(
        private readonly AdminFinanceFundSettingService $fundSettings,
        private readonly AdminFinanceMaintenanceLedgerService $ledger,
    ) {}

    /**
     * @param  array{period?: string, from?: ?string, to?: ?string}  $filters
     * @return array<string, mixed>
     */
    public function screenData(array $filters = []): array
    {
        $setting = $this->fundSettings->current();
        $effectiveDate = $setting?->opening_balance_effective_date
            ? Carbon::parse($setting->opening_balance_effective_date)->startOfDay()
            : null;
        $openingBalance = (float) ($setting?->opening_balance ?? 0);

        $period = $filters['period'] ?? 'year';
        [$rangeFrom, $rangeTo, $periodLabel] = $this->resolveDateRange($period, $filters, $effectiveDate);

        $lifetimeCollections = $this->collectionsTotal(
            $effectiveDate ?? Carbon::create(2000, 1, 1),
            Carbon::today(),
            $effectiveDate,
        );
        $lifetimeExpenses = $this->expensesTotal(
            $effectiveDate ?? Carbon::create(2000, 1, 1),
            Carbon::today(),
            $effectiveDate,
        );
        $fundBalance = $openingBalance + $lifetimeCollections - $lifetimeExpenses;

        $periodCollections = $this->collectionsTotal($rangeFrom, $rangeTo, $effectiveDate);
        $periodExpenses = $this->expensesTotal($rangeFrom, $rangeTo, $effectiveDate);
        $periodMaintenance = $this->maintenanceCollected($rangeFrom, $rangeTo);
        $periodOtherCollections = max(0, $periodCollections - $periodMaintenance);
        $periodNet = $periodCollections - $periodExpenses;

        $previousRange = $this->previousPeriodRange($rangeFrom, $rangeTo, $period);
        $previousCollections = $this->collectionsTotal($previousRange['from'], $previousRange['to'], $effectiveDate);
        $previousExpenses = $this->expensesTotal($previousRange['from'], $previousRange['to'], $effectiveDate);

        $trend = $this->monthlyTrend($rangeFrom, $rangeTo, $effectiveDate);
        $collectionsByType = $this->collectionsByType($rangeFrom, $rangeTo, $effectiveDate);
        $expensesByTag = $this->expensesByTag($rangeFrom, $rangeTo, $effectiveDate);

        return [
            'is_configured' => $setting !== null,
            'formatted_opening_balance' => $this->fundSettings->formatMoney($openingBalance),
            'formatted_effective_date' => $effectiveDate?->format('d M Y'),
            'filters' => [
                'period' => $period,
                'from' => $rangeFrom->toDateString(),
                'to' => $rangeTo->toDateString(),
                'period_label' => $periodLabel,
            ],
            'fund_stats' => $this->buildFundStats(
                $fundBalance,
                $periodCollections,
                $periodExpenses,
                $periodNet,
                $periodMaintenance,
                $periodOtherCollections,
                $periodLabel,
                $previousCollections,
                $previousExpenses,
            ),
            'charts' => [
                'trend' => $trend,
                'collections_by_type' => $collectionsByType,
                'expenses_by_tag' => $expensesByTag,
                'currency_symbol' => '₹',
            ],
            'recent_collections' => $this->recentCollections($rangeFrom, $rangeTo),
            'recent_expenses' => $this->recentExpenses($rangeFrom, $rangeTo),
        ];
    }

    /**
     * @param  array{period?: string, from?: ?string, to?: ?string}  $filters
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    private function resolveDateRange(string $period, array $filters, ?Carbon $effectiveDate): array
    {
        $today = Carbon::today();

        [$from, $to, $labelKey] = match ($period) {
            'month' => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth(), 'finance_overview_period_month'],
            'last_month' => [
                $today->copy()->subMonth()->startOfMonth(),
                $today->copy()->subMonth()->endOfMonth(),
                'finance_overview_period_last_month',
            ],
            'quarter' => [$today->copy()->firstOfQuarter(), $today->copy()->lastOfQuarter(), 'finance_overview_period_quarter'],
            'all' => [
                $effectiveDate?->copy() ?? $today->copy()->subYears(5)->startOfYear(),
                $today->copy(),
                'finance_overview_period_all',
            ],
            'custom' => [
                Carbon::parse($filters['from'] ?? $today->copy()->startOfYear()),
                Carbon::parse($filters['to'] ?? $today),
                'finance_overview_period_custom',
            ],
            default => [$today->copy()->startOfYear(), $today->copy()->endOfYear(), 'finance_overview_period_year'],
        };

        if ($effectiveDate && $from->lt($effectiveDate)) {
            $from = $effectiveDate->copy();
        }

        if ($to->gt($today)) {
            $to = $today->copy();
        }

        if ($from->gt($to)) {
            $from = $to->copy();
        }

        $label = $period === 'custom'
            ? __('messages.finance_overview_period_custom_label', [
                'from' => $from->format('d M Y'),
                'to' => $to->format('d M Y'),
            ])
            : __('messages.'.$labelKey, [
                'month' => $from->format('F Y'),
                'year' => (string) $from->year,
                'quarter' => (string) $from->quarter,
            ]);

        return [$from, $to, $label];
    }

    /**
     * @return array{from: Carbon, to: Carbon}
     */
    private function previousPeriodRange(Carbon $from, Carbon $to, string $period): array
    {
        $days = max(1, $from->diffInDays($to) + 1);

        return [
            'from' => $from->copy()->subDays($days),
            'to' => $from->copy()->subDay(),
        ];
    }

    private function collectionsTotal(Carbon $from, Carbon $to, ?Carbon $effectiveDate): float
    {
        $other = (float) FinanceCollection::query()
            ->where('collection_type', '!=', FinanceCollectionType::Maintenance->value)
            ->whereBetween('received_on', [$from->toDateString(), $to->toDateString()])
            ->when($effectiveDate, fn ($query) => $query->whereDate('received_on', '>=', $effectiveDate))
            ->sum('amount');

        return $other + $this->maintenanceCollected($from, $to);
    }

    private function maintenanceCollected(Carbon $from, Carbon $to): float
    {
        return $this->ledger->totalMaintenanceCollected($from, $to);
    }

    private function expensesTotal(Carbon $from, Carbon $to, ?Carbon $effectiveDate): float
    {
        return (float) FinanceExpense::query()
            ->whereBetween('paid_on', [$from->toDateString(), $to->toDateString()])
            ->when($effectiveDate, fn ($query) => $query->whereDate('paid_on', '>=', $effectiveDate))
            ->sum('amount');
    }

    /**
     * @return list<array{key: string, label: string, value: string, value_exact: string, hint: ?string, tone: string, icon: string, trend: ?array{direction: string, label: string}}>
     */
    private function buildFundStats(
        float $fundBalance,
        float $periodCollections,
        float $periodExpenses,
        float $periodNet,
        float $periodMaintenance,
        float $periodOtherCollections,
        string $periodLabel,
        float $previousCollections,
        float $previousExpenses,
    ): array {
        return [
            [
                'key' => 'fund_balance',
                'label' => __('messages.finance_stat_fund_balance'),
                'value' => $this->fundSettings->formatCompactMoney($fundBalance),
                'value_exact' => $this->fundSettings->formatMoney($fundBalance),
                'hint' => __('messages.finance_stat_fund_balance_hint'),
                'tone' => $fundBalance >= 0 ? 'primary' : 'danger',
                'icon' => 'wallet',
                'trend' => null,
            ],
            [
                'key' => 'collections_period',
                'label' => __('messages.finance_stat_collections_period'),
                'value' => $this->fundSettings->formatCompactMoney($periodCollections),
                'value_exact' => $this->fundSettings->formatMoney($periodCollections),
                'hint' => __('messages.finance_stat_collections_period_hint', ['period' => $periodLabel]),
                'tone' => 'income',
                'icon' => 'collections',
                'trend' => $this->trendMeta($periodCollections, $previousCollections),
            ],
            [
                'key' => 'expenses_period',
                'label' => __('messages.finance_stat_expenses_period'),
                'value' => $this->fundSettings->formatCompactMoney($periodExpenses),
                'value_exact' => $this->fundSettings->formatMoney($periodExpenses),
                'hint' => __('messages.finance_stat_expenses_period_hint', ['period' => $periodLabel]),
                'tone' => 'expense',
                'icon' => 'expenses',
                'trend' => $this->trendMeta($periodExpenses, $previousExpenses, invert: true),
            ],
            [
                'key' => 'net_period',
                'label' => __('messages.finance_stat_net_period'),
                'value' => $this->fundSettings->formatCompactMoney($periodNet),
                'value_exact' => $this->fundSettings->formatMoney($periodNet),
                'hint' => __('messages.finance_stat_net_period_hint', ['period' => $periodLabel]),
                'tone' => $periodNet >= 0 ? 'income' : 'danger',
                'icon' => 'surplus',
                'trend' => null,
            ],
            [
                'key' => 'maintenance_period',
                'label' => __('messages.finance_stat_maintenance_period'),
                'value' => $this->fundSettings->formatCompactMoney($periodMaintenance),
                'value_exact' => $this->fundSettings->formatMoney($periodMaintenance),
                'hint' => __('messages.finance_stat_maintenance_period_hint', ['period' => $periodLabel]),
                'tone' => 'neutral',
                'icon' => 'maintenance',
                'trend' => null,
            ],
            [
                'key' => 'other_collections_period',
                'label' => __('messages.finance_stat_other_collections_period'),
                'value' => $this->fundSettings->formatCompactMoney($periodOtherCollections),
                'value_exact' => $this->fundSettings->formatMoney($periodOtherCollections),
                'hint' => __('messages.finance_stat_other_collections_period_hint', ['period' => $periodLabel]),
                'tone' => 'accent',
                'icon' => 'other',
                'trend' => null,
            ],
        ];
    }

    /**
     * @return array{direction: string, label: string}|null
     */
    private function trendMeta(float $current, float $previous, bool $invert = false): ?array
    {
        if ($previous <= 0) {
            return null;
        }

        $change = (($current - $previous) / $previous) * 100;
        $positive = $change >= 0;
        $direction = ($invert ? ! $positive : $positive) ? 'up' : 'down';

        return [
            'direction' => $direction,
            'label' => sprintf('%s%.1f%%', $change >= 0 ? '+' : '', $change),
        ];
    }

    /**
     * @return array{labels: list<string>, collections: list<float>, expenses: list<float>, net: list<float>}
     */
    private function monthlyTrend(Carbon $from, Carbon $to, ?Carbon $effectiveDate): array
    {
        $labels = [];
        $collections = [];
        $expenses = [];
        $net = [];

        $cursor = $from->copy()->startOfMonth();
        $end = $to->copy()->endOfMonth();

        while ($cursor->lte($end)) {
            $monthStart = $cursor->copy()->startOfMonth()->max($from);
            $monthEnd = $cursor->copy()->endOfMonth()->min($to);

            $monthCollections = $this->collectionsTotal($monthStart, $monthEnd, $effectiveDate);
            $monthExpenses = $this->expensesTotal($monthStart, $monthEnd, $effectiveDate);

            $labels[] = $cursor->format('M y');
            $collections[] = round($monthCollections, 2);
            $expenses[] = round($monthExpenses, 2);
            $net[] = round($monthCollections - $monthExpenses, 2);

            $cursor->addMonth();
        }

        if ($labels === []) {
            $labels[] = $from->format('M y');
            $collections[] = 0.0;
            $expenses[] = 0.0;
            $net[] = 0.0;
        }

        return compact('labels', 'collections', 'expenses', 'net');
    }

    /**
     * @return array{labels: list<string>, values: list<float>, colors: list<string>}
     */
    private function collectionsByType(Carbon $from, Carbon $to, ?Carbon $effectiveDate): array
    {
        $maintenance = $this->maintenanceCollected($from, $to);

        $others = FinanceCollection::query()
            ->where('collection_type', '!=', FinanceCollectionType::Maintenance->value)
            ->whereBetween('received_on', [$from->toDateString(), $to->toDateString()])
            ->when($effectiveDate, fn ($query) => $query->whereDate('received_on', '>=', $effectiveDate))
            ->get()
            ->groupBy(fn (FinanceCollection $item) => $item->collection_type instanceof FinanceCollectionType
                ? $item->collection_type->value
                : (string) $item->collection_type);

        $rows = collect([
            [
                'label' => __('messages.finance_collection_maintenance'),
                'value' => $maintenance,
            ],
        ]);

        foreach ($others as $type => $items) {
            $enum = FinanceCollectionType::tryFrom((string) $type) ?? FinanceCollectionType::Other;
            $rows->push([
                'label' => $enum->label(),
                'value' => (float) $items->sum('amount'),
            ]);
        }

        return $this->chartDataset($rows->filter(fn (array $row) => $row['value'] > 0)->values());
    }

    /**
     * @return array{labels: list<string>, values: list<float>, colors: list<string>}
     */
    private function expensesByTag(Carbon $from, Carbon $to, ?Carbon $effectiveDate): array
    {
        $grouped = FinanceExpense::query()
            ->whereBetween('paid_on', [$from->toDateString(), $to->toDateString()])
            ->when($effectiveDate, fn ($query) => $query->whereDate('paid_on', '>=', $effectiveDate))
            ->get()
            ->groupBy(fn (FinanceExpense $item) => $item->expense_tag instanceof FinanceExpenseTag
                ? $item->expense_tag->value
                : (string) $item->expense_tag);

        $rows = $grouped->map(function (Collection $items, string $tag): array {
            $enum = FinanceExpenseTag::tryFrom($tag) ?? FinanceExpenseTag::Others;

            return [
                'label' => $enum->label(),
                'value' => (float) $items->sum('amount'),
            ];
        })->sortByDesc('value')->values();

        if ($rows->count() > 6) {
            $top = $rows->take(5);
            $rest = $rows->slice(5)->sum('value');
            $rows = $top->push([
                'label' => __('messages.finance_overview_other_tags'),
                'value' => (float) $rest,
            ]);
        }

        return $this->chartDataset($rows->filter(fn (array $row) => $row['value'] > 0)->values());
    }

    /**
     * @param  Collection<int, array{label: string, value: float}>  $rows
     * @return array{labels: list<string>, values: list<float>, colors: list<string>}
     */
    private function chartDataset(Collection $rows): array
    {
        if ($rows->isEmpty()) {
            return [
                'labels' => [__('messages.finance_overview_no_data')],
                'values' => [0.0],
                'colors' => [self::CHART_COLORS[5]],
            ];
        }

        return [
            'labels' => $rows->pluck('label')->all(),
            'values' => $rows->pluck('value')->map(fn ($v) => round((float) $v, 2))->all(),
            'colors' => collect($rows)->keys()->map(fn ($i) => self::CHART_COLORS[$i % count(self::CHART_COLORS)])->all(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function recentCollections(Carbon $from, Carbon $to): array
    {
        $ledgerRows = MaintenanceMonthlyEntry::query()
            ->whereHas('mainMember')
            ->with(['mainMember'])
            ->where('amount_paid', '>', 0)
            ->whereBetween('paid_on', [$from->toDateString(), $to->toDateString()])
            ->latest('paid_on')
            ->latest('id')
            ->limit(6)
            ->get()
            ->map(fn (MaintenanceMonthlyEntry $item): array => $this->mapCollectionRow(
                $item->paid_on?->format('d M Y') ?? $item->billing_month->format('d M Y'),
                __('messages.finance_collection_maintenance'),
                $item->mainMember?->houseLabel() ?? '—',
                $item->mainMember?->fullName(),
                (float) $item->amount_paid,
            ));

        $otherRows = FinanceCollection::query()
            ->with(['mainMember'])
            ->where('collection_type', '!=', FinanceCollectionType::Maintenance->value)
            ->whereBetween('received_on', [$from->toDateString(), $to->toDateString()])
            ->latest('received_on')
            ->latest('id')
            ->limit(6)
            ->get()
            ->map(function (FinanceCollection $item): array {
                $type = $item->collection_type instanceof FinanceCollectionType
                    ? $item->collection_type
                    : FinanceCollectionType::from((string) $item->collection_type);

                return $this->mapCollectionRow(
                    $item->received_on->format('d M Y'),
                    $type->label(),
                    $item->mainMember?->houseLabel() ?? '—',
                    $item->mainMember?->fullName(),
                    (float) $item->amount,
                );
            });

        return $ledgerRows->concat($otherRows)->sortByDesc('date')->take(8)->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapCollectionRow(string $date, string $typeLabel, string $house, ?string $mainMember, float $amount): array
    {
        return [
            'date' => $date,
            'type_label' => $typeLabel,
            'house' => $house,
            'main_member' => $mainMember,
            'amount' => $this->fundSettings->formatMoney($amount),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function recentExpenses(Carbon $from, Carbon $to): array
    {
        return FinanceExpense::query()
            ->whereBetween('paid_on', [$from->toDateString(), $to->toDateString()])
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
