<?php

namespace App\Services\Admin;

use App\Enums\FinancePaymentReceiptKind;
use App\Enums\MaintenanceMonthEntryStatus;
use App\Models\FinancePaymentReceipt;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;

class AdminFinanceMyPaymentService
{
    public function __construct(
        private readonly AdminFinanceFundSettingService $fundSettings,
        private readonly FinancePaymentReceiptService $receipts,
    ) {}

    public function canView(User $actor): bool
    {
        if ($actor->isResidentMainMember()) {
            return true;
        }

        return $actor->isSecurityGuard() && $actor->workerRecord()->exists();
    }

    public function canDownloadReceipt(FinancePaymentReceipt $receipt, User $actor): bool
    {
        return $this->receipts->canDownload($receipt, $actor);
    }

    /**
     * @return array{
     *     payments: LengthAwarePaginator,
     *     total_received: string,
     *     house: ?string,
     *     view_mode: 'member'|'worker_salary',
     *     profile_label: ?string,
     *     profile_value: ?string
     * }
     */
    public function screenData(User $actor): array
    {
        $isWorkerView = $actor->isSecurityGuard() && $actor->workerRecord;

        $query = FinancePaymentReceipt::query()
            ->withCount('lines')
            ->orderByDesc('paid_on')
            ->orderByDesc('id');

        if ($isWorkerView) {
            $query->where('worker_id', $actor->workerRecord->id)
                ->where('receipt_kind', FinancePaymentReceiptKind::WorkerSalary->value);
        } else {
            $query->where('main_member_id', $actor->id);
        }

        $receipts = $query->get();
        $rows = $receipts->map(fn (FinancePaymentReceipt $receipt): array => $this->mapReceiptRow($receipt));

        $total = (float) $receipts->sum('total_amount');

        return [
            'payments' => $this->paginateRows($rows),
            'total_received' => $this->fundSettings->formatMoney($total),
            'house' => $isWorkerView ? null : $actor->houseLabel(),
            'view_mode' => $isWorkerView ? 'worker_salary' : 'member',
            'profile_label' => $isWorkerView ? __('messages.workers_name') : null,
            'profile_value' => $isWorkerView ? $actor->workerRecord?->name : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapReceiptRow(FinancePaymentReceipt $receipt): array
    {
        $kind = $receipt->receipt_kind;
        $lineCount = (int) $receipt->lines_count;
        $statusLabel = null;

        if ($kind === FinancePaymentReceiptKind::Maintenance && $lineCount > 1) {
            $statusLabel = __('messages.finance_receipt_months_count', ['count' => $lineCount]);
        }

        return [
            'receipt_id' => $receipt->id,
            'received_on' => $receipt->paid_on->format('d M Y'),
            'type_label' => $kind->label(),
            'amount' => $this->fundSettings->formatMoney($receipt->total_amount),
            'status_label' => $statusLabel,
            'reference' => $receipt->reference,
            'notes' => $receipt->notes,
            'receipt_number' => $receipt->receipt_number,
            'sort_date' => $receipt->paid_on->format('Y-m-d'),
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     */
    private function paginateRows(Collection $rows): LengthAwarePaginator
    {
        $page = max(1, (int) request()->integer('page', 1));
        $perPage = 20;
        $items = $rows->slice(($page - 1) * $perPage, $perPage)->values();

        return new Paginator(
            $items,
            $rows->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()],
        );
    }
}
