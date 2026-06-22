<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\FinancePaymentReceipt;
use App\Services\Admin\AdminFinanceMyPaymentService;
use App\Services\Admin\FinancePaymentReceiptService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class MyPaymentController extends Controller
{
    public function __construct(
        private readonly AdminFinanceMyPaymentService $payments,
        private readonly FinancePaymentReceiptService $receipts,
    ) {}

    public function index(): View|RedirectResponse
    {
        $actor = auth()->user();

        if (! $this->payments->canView($actor)) {
            abort(403, __('messages.admin_module_forbidden'));
        }

        return view('admin.finance.my-payments', $this->payments->screenData($actor));
    }

    public function downloadReceipt(FinancePaymentReceipt $receipt): Response
    {
        $actor = auth()->user();

        if (! $actor || ! $this->receipts->canDownload($receipt, $actor)) {
            abort(403, __('messages.admin_module_forbidden'));
        }

        $pdf = Pdf::loadView('pdf.payment-receipt', $this->receipts->pdfViewData($receipt))
            ->setPaper('a4');

        return $pdf->download($receipt->downloadFilename());
    }
}
