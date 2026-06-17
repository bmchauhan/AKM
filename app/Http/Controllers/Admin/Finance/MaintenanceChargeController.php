<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Admin\Finance\Concerns\OpensFinanceFormModal;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Finance\OpenEditMaintenanceChargeRequest;
use App\Http\Requests\Admin\Finance\StoreMaintenanceChargeRequest;
use App\Http\Requests\Admin\Finance\UpdateMaintenanceChargeRequest;
use App\Models\MaintenanceChargeSetting;
use App\Services\Admin\AdminFinanceMaintenanceChargeService;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaintenanceChargeController extends Controller
{
    use OpensFinanceFormModal;

    public function __construct(
        private readonly AdminFinanceMaintenanceChargeService $maintenanceCharges,
    ) {}

    public function index(Request $request): View
    {
        $editingCharge = $this->maintenanceCharges->editingCharge();

        return view('admin.finance.maintenance-charges.index', [
            ...$this->maintenanceCharges->listForScreen(),
            'statuses' => $this->maintenanceCharges->statusesForSelect(),
            'openAddChargeModal' => $this->shouldOpenFinanceModal($request, 'maintenance-charge'),
            'openEditChargeModal' => $editingCharge !== null
                || $this->shouldOpenFinanceModal($request, 'maintenance-charge-edit'),
            'editingCharge' => $editingCharge,
        ]);
    }

    public function store(StoreMaintenanceChargeRequest $request): RedirectResponse
    {
        $this->maintenanceCharges->create($request->user(), $request->validated());

        Toast::success(__('messages.finance_maintenance_charge_saved'));

        return redirect()->route('admin.finance.maintenance-charges.index');
    }

    public function openEdit(OpenEditMaintenanceChargeRequest $request): RedirectResponse
    {
        $charge = MaintenanceChargeSetting::query()->findOrFail($request->integer('maintenance_charge_id'));
        $this->maintenanceCharges->rememberEditingCharge($charge);

        return redirect()->route('admin.finance.maintenance-charges.index');
    }

    public function cancelEdit(): RedirectResponse
    {
        $this->maintenanceCharges->clearEditingCharge();

        return redirect()->route('admin.finance.maintenance-charges.index');
    }

    public function update(UpdateMaintenanceChargeRequest $request): RedirectResponse
    {
        $charge = $this->maintenanceCharges->editingCharge();

        if (! $charge) {
            return redirect()->route('admin.finance.maintenance-charges.index');
        }

        $this->maintenanceCharges->update($charge, $request->validated());
        $this->maintenanceCharges->clearEditingCharge();

        Toast::success(__('messages.finance_maintenance_charge_updated'));

        return redirect()->route('admin.finance.maintenance-charges.index');
    }
}
