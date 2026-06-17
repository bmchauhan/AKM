<?php

namespace App\Http\Controllers\Admin\Finance\Concerns;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait RedirectsMaintenanceLedgerContext
{
    /**
     * @return array<string, string>|null
     */
    protected function houseReturnParams(Request $request): ?array
    {
        $context = $request->input('return_context', $request->query('return_context'));

        if ($context !== 'house') {
            return null;
        }

        $house = trim($request->input('house', $request->query('house', '')));

        if ($house === '') {
            return null;
        }

        $status = trim($request->input('status_filter', $request->query('status', 'outstanding')));

        $params = [
            'house' => $house,
            'status' => $status !== '' ? $status : 'outstanding',
        ];

        if ($request->query('open') === 'ledger-edit') {
            $params['open'] = 'ledger-edit';
        }

        return $params;
    }

    /**
     * @param  array<string, mixed>  $monthParams
     */
    protected function redirectAfterLedgerAction(Request $request, array $monthParams = []): RedirectResponse
    {
        $houseParams = $this->houseReturnParams($request);

        if ($houseParams) {
            return redirect()->route('admin.finance.maintenance-ledger.house', $houseParams);
        }

        return redirect()->route('admin.finance.maintenance-ledger.index', $monthParams);
    }
}
