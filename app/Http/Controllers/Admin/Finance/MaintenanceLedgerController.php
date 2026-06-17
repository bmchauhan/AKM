<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Admin\Finance\Concerns\OpensFinanceFormModal;
use App\Http\Controllers\Admin\Finance\Concerns\RedirectsMaintenanceLedgerContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Finance\ApplyBulkMaintenancePaymentRequest;
use App\Http\Requests\Admin\Finance\GenerateMaintenanceLedgerMonthRequest;
use App\Http\Requests\Admin\Finance\MarkMaintenanceLedgerPaidRequest;
use App\Http\Requests\Admin\Finance\OpenEditMaintenanceLedgerEntryRequest;
use App\Http\Requests\Admin\Finance\UpdateMaintenanceLedgerEntryRequest;
use App\Models\MaintenanceMonthlyEntry;
use App\Models\User;
use App\Services\Admin\AdminFinanceCollectionService;
use App\Services\Admin\AdminFinanceMaintenanceLedgerService;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MaintenanceLedgerController extends Controller
{
    use OpensFinanceFormModal;
    use RedirectsMaintenanceLedgerContext;

    public function __construct(
        private readonly AdminFinanceMaintenanceLedgerService $ledger,
    ) {}

    public function index(Request $request): View
    {
        $month = $request->string('month')->toString() ?: now()->format('Y-m');
        $actor = $request->user();

        $this->ledger->refreshAllOverdueStatuses();

        if ($actor?->can('finance.update')) {
            $this->ledger->ensureMonthGenerated($month, $actor);
        }

        $editingEntry = $this->ledger->editingEntryForMonth($month);
        $openEditEntryModal = $editingEntry !== null
            && $this->shouldOpenFinanceModal($request, 'ledger-edit');

        if ($editingEntry && ! $openEditEntryModal) {
            $this->ledger->clearEditingEntry();
            $editingEntry = null;
        }

        $screen = $this->ledger->listForScreen($month, [
            'status' => $request->string('status')->toString(),
            'house' => $request->string('house')->toString(),
            'name' => $request->string('name')->toString(),
        ]);

        return view('admin.finance.maintenance-ledger.index', [
            ...$screen,
            'statusOptions' => $this->ledger->statusesForSelect(),
            'paymentModes' => app(AdminFinanceCollectionService::class)->paymentModesForSelect(),
            'editingEntry' => $editingEntry,
            'openEditEntryModal' => $openEditEntryModal,
        ]);
    }

    public function house(Request $request): View
    {
        $this->ledger->refreshAllOverdueStatuses();

        $house = $request->string('house')->toString();
        $name = $request->string('name')->toString();
        $memberId = $request->integer('member_id');
        $hasSearched = filled($house) || filled($name) || $memberId > 0;
        $status = $request->has('status')
            ? $request->string('status')->toString()
            : 'outstanding';

        $editingEntry = $this->ledger->editingEntry();
        $openEditEntryModal = $editingEntry !== null
            && $this->shouldOpenFinanceModal($request, 'ledger-edit');

        if ($editingEntry && ! $openEditEntryModal) {
            $this->ledger->clearEditingEntry();
            $editingEntry = null;
        }

        $screen = $this->ledger->houseHistoryForScreen([
            'house' => $house,
            'name' => $name,
            'member_id' => $memberId,
            'status' => $status,
        ], $hasSearched);

        $resolvedMember = $screen['member'] ?? null;
        $houseReturnHouse = $resolvedMember?->houseLabel() ?? $house;

        $houseReturn = $hasSearched && ($resolvedMember || filled($house) || filled($name))
            ? [
                'return_context' => 'house',
                'house' => $houseReturnHouse,
                'status_filter' => $screen['filters']['status'],
            ]
            : [];

        return view('admin.finance.maintenance-ledger.house', [
            ...$screen,
            'statusOptions' => $this->ledger->houseStatusFiltersForSelect(),
            'paymentModes' => app(AdminFinanceCollectionService::class)->paymentModesForSelect(),
            'editingEntry' => $editingEntry,
            'openEditEntryModal' => $openEditEntryModal,
            'houseReturn' => $houseReturn,
            'cancelEditUrl' => $resolvedMember
                ? route('admin.finance.maintenance-ledger.cancel-edit', [
                    'return_context' => 'house',
                    'house' => $houseReturnHouse,
                    'status' => $screen['filters']['status'],
                ])
                : null,
            'allocationEntries' => $screen['allocation_entries'] ?? [],
            'totalOutstandingRaw' => $screen['total_outstanding_raw'] ?? 0,
        ]);
    }

    public function applyBulkPayment(ApplyBulkMaintenancePaymentRequest $request): RedirectResponse
    {
        $member = User::query()->findOrFail($request->integer('main_member_id'));

        $result = $this->ledger->applyBulkPayment(
            $member,
            $request->user(),
            (float) $request->input('payment_amount'),
            $request->safe()->only(['paid_on', 'payment_mode', 'reference', 'notes']),
        );

        Toast::success(__('messages.finance_ledger_bulk_success', [
            'count' => $result['applied_count'],
            'amount' => number_format((float) $result['preview']['allocated_total'], 2),
        ]));

        return redirect()->route('admin.finance.maintenance-ledger.house', array_filter([
            'house' => $request->string('house')->toString() ?: $member->houseLabel(),
            'status' => $request->string('status_filter')->toString() ?: 'outstanding',
        ]));
    }

    public function generate(GenerateMaintenanceLedgerMonthRequest $request): RedirectResponse
    {
        $result = $this->ledger->generateMonth(
            $request->string('month')->toString(),
            $request->user(),
        );

        Toast::success(__('messages.finance_ledger_generated', [
            'created' => $result['created'],
            'skipped' => $result['skipped'],
        ]));

        return redirect()->route('admin.finance.maintenance-ledger.index', [
            'month' => $request->input('month'),
        ]);
    }

    public function syncMissing(GenerateMaintenanceLedgerMonthRequest $request): RedirectResponse
    {
        $result = $this->ledger->syncMissingHouses(
            $request->string('month')->toString(),
            $request->user(),
        );

        Toast::success(__('messages.finance_ledger_synced', ['created' => $result['created']]));

        return redirect()->route('admin.finance.maintenance-ledger.index', [
            'month' => $request->input('month'),
        ]);
    }

    public function openEdit(OpenEditMaintenanceLedgerEntryRequest $request): RedirectResponse
    {
        $entry = MaintenanceMonthlyEntry::query()->findOrFail($request->integer('entry_id'));
        $this->ledger->rememberEditingEntry($entry);

        $houseParams = $this->houseReturnParams($request);
        if ($houseParams) {
            return redirect()->route('admin.finance.maintenance-ledger.house', [
                ...$houseParams,
                'open' => 'ledger-edit',
            ]);
        }

        return redirect()->route('admin.finance.maintenance-ledger.index', [
            'month' => $entry->billing_month->format('Y-m'),
            'open' => 'ledger-edit',
        ]);
    }

    public function cancelEdit(Request $request): RedirectResponse
    {
        $entry = $this->ledger->editingEntry();
        $month = $entry?->billing_month->format('Y-m') ?? $request->string('month')->toString();
        $this->ledger->clearEditingEntry();

        $houseParams = $this->houseReturnParams($request);
        if ($houseParams) {
            unset($houseParams['open']);

            return redirect()->route('admin.finance.maintenance-ledger.house', $houseParams);
        }

        return redirect()->route('admin.finance.maintenance-ledger.index', array_filter([
            'month' => $month ?: null,
        ]));
    }

    public function update(UpdateMaintenanceLedgerEntryRequest $request): RedirectResponse
    {
        $entry = $this->ledger->editingEntry();

        if (! $entry) {
            return $this->redirectAfterLedgerAction($request);
        }

        $this->ledger->updateEntry($entry, $request->user(), $request->validated());
        $month = $entry->billing_month->format('Y-m');
        $this->ledger->clearEditingEntry();

        Toast::success(__('messages.finance_ledger_updated'));

        return $this->redirectAfterLedgerAction($request, ['month' => $month]);
    }

    public function markPaid(MarkMaintenanceLedgerPaidRequest $request): RedirectResponse
    {
        $entry = MaintenanceMonthlyEntry::query()->findOrFail($request->integer('entry_id'));

        $this->ledger->markPaid($entry, $request->user(), $request->validated());

        Toast::success(__('messages.finance_ledger_marked_paid'));

        return $this->redirectAfterLedgerAction($request, [
            'month' => $entry->billing_month->format('Y-m'),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $month = $request->string('month')->toString() ?: now()->format('Y-m');
        $rows = $this->ledger->exportRows($month, [
            'status' => $request->string('status')->toString(),
            'house' => $request->string('house')->toString(),
            'name' => $request->string('name')->toString(),
        ]);

        $filename = 'maintenance-ledger-'.$month.'.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                __('messages.users_house'),
                __('messages.finance_main_member'),
                __('messages.finance_maintenance_charge_amount'),
                __('messages.finance_amount_received'),
                __('messages.finance_maintenance_status'),
                __('messages.finance_paid_on'),
                __('messages.finance_notes'),
            ]);

            foreach ($rows as $entry) {
                $status = $entry->status instanceof \App\Enums\MaintenanceMonthEntryStatus
                    ? $entry->status
                    : \App\Enums\MaintenanceMonthEntryStatus::from((string) $entry->status);

                fputcsv($handle, [
                    $entry->mainMember?->houseLabel() ?? '',
                    $entry->mainMember?->fullName() ?? '',
                    number_format((float) $entry->charge_amount, 2, '.', ''),
                    number_format((float) $entry->amount_paid, 2, '.', ''),
                    $status->label(),
                    $entry->paid_on?->format('Y-m-d') ?? '',
                    $entry->notes ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
