<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Finance\UpdateFundSettingRequest;
use App\Services\Admin\AdminFinanceFundSettingService;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FundSettingController extends Controller
{
    public function __construct(
        private readonly AdminFinanceFundSettingService $fundSettings,
    ) {}

    public function show(): View
    {
        return view('admin.finance.fund-setting', [
            ...$this->fundSettings->screenData(),
            'defaultEffectiveDate' => $this->fundSettings->defaultEffectiveDate(),
        ]);
    }

    public function update(UpdateFundSettingRequest $request): RedirectResponse
    {
        $this->fundSettings->save($request->user(), $request->validated());

        Toast::success(__('messages.finance_fund_setting_saved'));

        return redirect()->route('admin.finance.fund-setting.show');
    }
}
