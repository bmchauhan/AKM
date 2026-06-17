<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminFinanceMaintenanceChargeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaintenanceChargeLookupController extends Controller
{
    public function __construct(
        private readonly AdminFinanceMaintenanceChargeService $maintenanceCharges,
    ) {}

    public function show(Request $request): JsonResponse
    {
        abort_unless(
            $request->user()?->can('finance.create') || $request->user()?->can('finance.update'),
            403,
        );

        $request->validate([
            'received_on' => ['required', 'date'],
        ]);

        $payload = $this->maintenanceCharges->chargeLookup(
            $request->string('received_on')->toString(),
        );

        if (! $payload) {
            return response()->json(['message' => __('messages.finance_maintenance_charge_not_found')], 404);
        }

        return response()->json($payload);
    }
}
