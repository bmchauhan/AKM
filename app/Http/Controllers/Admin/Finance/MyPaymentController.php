<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminFinanceMyPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MyPaymentController extends Controller
{
    public function __construct(
        private readonly AdminFinanceMyPaymentService $payments,
    ) {}

    public function index(): View|RedirectResponse
    {
        $actor = auth()->user();

        if (! $this->payments->canView($actor)) {
            abort(403, __('messages.admin_module_forbidden'));
        }

        return view('admin.finance.my-payments', $this->payments->screenData($actor));
    }
}
