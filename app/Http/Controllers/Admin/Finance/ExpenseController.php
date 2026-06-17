<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Admin\Finance\Concerns\OpensFinanceFormModal;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Finance\DestroyExpenseRequest;
use App\Http\Requests\Admin\Finance\OpenEditExpenseRequest;
use App\Http\Requests\Admin\Finance\StoreExpenseRequest;
use App\Http\Requests\Admin\Finance\UpdateExpenseRequest;
use App\Models\FinanceExpense;
use App\Services\Admin\AdminFinanceExpenseService;
use App\Services\Admin\AdminFinanceExportService;
use App\Services\Admin\AdminWorkerService;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseController extends Controller
{
    use OpensFinanceFormModal;

    public function __construct(
        private readonly AdminFinanceExpenseService $expenses,
        private readonly AdminFinanceExportService $exports,
        private readonly AdminWorkerService $workers,
    ) {}

    public function index(Request $request): View
    {
        $editingExpense = $this->expenses->editingExpense();

        return view('admin.finance.expenses.index', [
            ...$this->expenses->listForScreen([
                'expense_tag' => $request->string('expense_tag')->toString(),
                'date_from' => $request->string('date_from')->toString(),
                'date_to' => $request->string('date_to')->toString(),
            ]),
            'openExpenseModal' => $this->shouldOpenFinanceModal($request, 'expense'),
            'editingExpense' => $editingExpense,
            'openEditExpenseModal' => $editingExpense !== null
                || $this->shouldOpenFinanceModal($request, 'expense-edit'),
            'workersGrouped' => $this->workers->activeWorkersGroupedForExpense(),
            'workerSalaryLookupUrl' => route('admin.finance.worker-salary.show'),
        ]);
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $this->expenses->create($request->user(), $request->validated());

        Toast::success(__('messages.finance_expense_created'));

        return back();
    }

    public function openEdit(OpenEditExpenseRequest $request): RedirectResponse
    {
        $expense = FinanceExpense::query()->findOrFail($request->integer('expense_id'));
        $this->expenses->rememberEditingExpense($expense);

        return redirect()->route('admin.finance.expenses.index');
    }

    public function cancelEdit(): RedirectResponse
    {
        $this->expenses->clearEditingExpense();

        return redirect()->route('admin.finance.expenses.index');
    }

    public function update(UpdateExpenseRequest $request): RedirectResponse
    {
        $expenseId = $request->editingExpenseId();

        if (! $expenseId) {
            return redirect()->route('admin.finance.expenses.index');
        }

        $expense = FinanceExpense::query()->findOrFail($expenseId);
        $this->expenses->update($expense, $request->validated());
        $this->expenses->clearEditingExpense();

        Toast::success(__('messages.finance_expense_updated'));

        return redirect()->route('admin.finance.expenses.index');
    }

    public function destroy(DestroyExpenseRequest $request): RedirectResponse
    {
        $expense = FinanceExpense::query()->findOrFail($request->integer('expense_id'));
        $this->expenses->delete($expense);

        if ((int) session(AdminFinanceExpenseService::SESSION_EDITING_EXPENSE) === $expense->id) {
            $this->expenses->clearEditingExpense();
        }

        Toast::success(__('messages.finance_expense_deleted'));

        return back();
    }

    public function export(Request $request): StreamedResponse
    {
        return $this->exports->expensesCsv([
            'expense_tag' => $request->string('expense_tag')->toString(),
            'date_from' => $request->string('date_from')->toString(),
            'date_to' => $request->string('date_to')->toString(),
        ]);
    }
}
