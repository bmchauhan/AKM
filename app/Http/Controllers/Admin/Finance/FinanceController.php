<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Enums\FinanceCollectionType;
use App\Http\Controllers\Admin\Finance\Concerns\OpensFinanceFormModal;
use App\Http\Controllers\Controller;
use App\Services\Admin\AdminFinanceCollectionService;
use App\Services\Admin\AdminFinanceExpenseService;
use App\Services\Admin\AdminFinanceOverviewService;
use App\Services\Admin\AdminWorkerService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceController extends Controller
{
    use OpensFinanceFormModal;

    public function __construct(
        private readonly AdminFinanceOverviewService $overview,
        private readonly AdminFinanceCollectionService $collections,
        private readonly AdminFinanceExpenseService $expenses,
        private readonly AdminWorkerService $workers,
    ) {}

    public function index(Request $request): View
    {
        $data = $this->overview->screenData();

        if ($request->user()?->can('finance.create')) {
            $data = [
                ...$data,
                ...$this->collections->createFormData(),
                'expenseTags' => $this->expenses->createFormData()['expenseTags'],
                'workersGrouped' => $this->workers->activeWorkersGroupedForExpense(),
                'workerSalaryLookupUrl' => route('admin.finance.worker-salary.show'),
                'maintenanceChargeLookupUrl' => route('admin.finance.maintenance-charge.show'),
                'defaultCollectionType' => FinanceCollectionType::ClubhouseBooking->value,
                'openCollectionModal' => $this->shouldOpenFinanceModal($request, 'collection'),
                'openExpenseModal' => $this->shouldOpenFinanceModal($request, 'expense'),
            ];
        }

        return view('admin.finance.overview', $data);
    }
}
