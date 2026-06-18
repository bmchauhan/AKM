<?php

namespace App\Services\Admin;

use App\Enums\MaintenanceMonthEntryStatus;
use App\Models\MaintenanceMonthlyEntry;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

class AdminFinanceMyPaymentService
{
    public function __construct(
        private readonly AdminFinanceFundSettingService $fundSettings,
    ) {}

    public function canView(User $actor): bool
    {
        return $actor->isMainMember();
    }

    /**
     * @return array{
     *     payments: LengthAwarePaginator,
     *     total_received: string,
     *     house: ?string
     * }
     */
    public function screenData(User $actor): array
    {
        $ledgerEntries = MaintenanceMonthlyEntry::query()
            ->where('main_member_id', $actor->id)
            ->orderByDesc('billing_month')
            ->get()
            ->map(fn (MaintenanceMonthlyEntry $item): array => [
                'received_on' => $item->billing_month->format('F Y'),
                'type_label' => __('messages.finance_collection_maintenance'),
                'amount' => $this->fundSettings->formatMoney($item->amount_paid),
                'status_label' => ($item->status instanceof MaintenanceMonthEntryStatus
                    ? $item->status
                    : MaintenanceMonthEntryStatus::from((string) $item->status))->label(),
                'reference' => $item->reference,
                'notes' => $item->notes,
                'sort_date' => $item->billing_month->format('Y-m-d'),
            ]);

        $otherPayments = \App\Models\FinanceCollection::query()
            ->where('main_member_id', $actor->id)
            ->where('collection_type', '!=', \App\Enums\FinanceCollectionType::Maintenance->value)
            ->latest('received_on')
            ->get()
            ->map(function ($item): array {
                $type = $item->collection_type;

                return [
                    'received_on' => $item->received_on->format('d M Y'),
                    'type_label' => $type->label(),
                    'amount' => $this->fundSettings->formatMoney($item->amount),
                    'status_label' => null,
                    'reference' => $item->reference,
                    'notes' => $item->notes,
                    'sort_date' => $item->received_on->format('Y-m-d'),
                ];
            });

        $merged = $ledgerEntries->concat($otherPayments)
            ->sortByDesc('sort_date')
            ->values();

        $page = max(1, (int) request()->integer('page', 1));
        $perPage = 20;
        $items = $merged->slice(($page - 1) * $perPage, $perPage)->values();

        $payments = new Paginator(
            $items,
            $merged->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()],
        );

        $total = (float) MaintenanceMonthlyEntry::query()
            ->where('main_member_id', $actor->id)
            ->sum('amount_paid');
        $total += (float) \App\Models\FinanceCollection::query()
            ->where('main_member_id', $actor->id)
            ->where('collection_type', '!=', \App\Enums\FinanceCollectionType::Maintenance->value)
            ->sum('amount');

        return [
            'payments' => $payments,
            'total_received' => $this->fundSettings->formatMoney($total),
            'house' => $actor->houseLabel(),
        ];
    }
}
