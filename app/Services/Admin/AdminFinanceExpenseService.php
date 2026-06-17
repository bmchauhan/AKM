<?php

namespace App\Services\Admin;

use App\Enums\FinanceExpenseTag;
use App\Models\FinanceExpense;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AdminFinanceExpenseService
{
    public const SESSION_EDITING_EXPENSE = 'admin.finance.editing_expense_id';

    public function __construct(
        private readonly AdminFinanceFundSettingService $fundSettings,
    ) {}

    /**
     * @param  array{expense_tag?: string, date_from?: string, date_to?: string}  $filters
     * @return array{
     *     expenses: LengthAwarePaginator,
     *     filters: array{expense_tag: string, date_from: string, date_to: string},
     *     expenseTags: list<array{value: string, label: string}>
     * }
     */
    public function listForScreen(array $filters = []): array
    {
        $normalized = $this->normalizeFilters($filters);

        $expenses = $this->filteredQuery($normalized)
            ->with(['recordedBy', 'worker'])
            ->latest('paid_on')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $expenses->getCollection()->transform(fn (FinanceExpense $item) => $this->mapListRow($item));

        return [
            'expenses' => $expenses,
            'filters' => $normalized,
            'expenseTags' => $this->expenseTagsForSelect(),
        ];
    }

    /**
     * @param  array{expense_tag?: string, date_from?: string, date_to?: string}  $filters
     */
    public function exportRows(array $filters = []): Collection
    {
        return $this->filteredQuery($this->normalizeFilters($filters))
            ->with(['recordedBy', 'worker'])
            ->latest('paid_on')
            ->latest('id')
            ->get();
    }

    /**
     * @param  array{expense_tag?: string, date_from?: string, date_to?: string}  $filters
     */
    public function filteredQuery(array $filters): Builder
    {
        $query = FinanceExpense::query();

        if (filled($filters['expense_tag'])) {
            $query->where('expense_tag', $filters['expense_tag']);
        }

        if (filled($filters['date_from'])) {
            $query->whereDate('paid_on', '>=', $filters['date_from']);
        }

        if (filled($filters['date_to'])) {
            $query->whereDate('paid_on', '<=', $filters['date_to']);
        }

        return $query;
    }

    /**
     * @param  array{expense_tag?: string, date_from?: string, date_to?: string}  $filters
     * @return array{expense_tag: string, date_from: string, date_to: string}
     */
    public function normalizeFilters(array $filters): array
    {
        return [
            'expense_tag' => (string) ($filters['expense_tag'] ?? ''),
            'date_from' => (string) ($filters['date_from'] ?? ''),
            'date_to' => (string) ($filters['date_to'] ?? ''),
        ];
    }

    /**
     * @return array{expenseTags: list<array{value: string, label: string}>}
     */
    public function createFormData(): array
    {
        return [
            'expenseTags' => $this->expenseTagsForSelect(),
        ];
    }

    public function rememberEditingExpense(FinanceExpense $expense): void
    {
        session([self::SESSION_EDITING_EXPENSE => $expense->id]);
    }

    public function editingExpense(): ?FinanceExpense
    {
        $expenseId = session(self::SESSION_EDITING_EXPENSE);

        if (! $expenseId) {
            return null;
        }

        return FinanceExpense::query()
            ->with(['recordedBy', 'worker'])
            ->find((int) $expenseId);
    }

    public function clearEditingExpense(): void
    {
        session()->forget(self::SESSION_EDITING_EXPENSE);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $actor, array $data): FinanceExpense
    {
        return FinanceExpense::query()->create([
            ...$this->resolveExpensePayload($data),
            'recorded_by_user_id' => $actor->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(FinanceExpense $expense, array $data): FinanceExpense
    {
        $expense->update($this->resolveExpensePayload($data));

        return $expense->fresh(['recordedBy', 'worker']);
    }

    public function delete(FinanceExpense $expense): void
    {
        $expense->delete();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function expenseTagsForSelect(): array
    {
        return collect(FinanceExpenseTag::cases())
            ->map(fn (FinanceExpenseTag $tag) => [
                'value' => $tag->value,
                'label' => $tag->label(),
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapListRow(FinanceExpense $item): array
    {
        $tag = $item->expense_tag instanceof FinanceExpenseTag
            ? $item->expense_tag
            : FinanceExpenseTag::from((string) $item->expense_tag);

        $salaryBreakdown = null;

        if ($tag->requiresWorker() && $item->salary_base_amount !== null) {
            $adjustment = (float) $item->salary_adjustment;
            $salaryBreakdown = $this->fundSettings->formatMoney($item->salary_base_amount);

            if ($adjustment !== 0.0) {
                $sign = $adjustment > 0 ? '+' : '−';
                $salaryBreakdown .= ' '.$sign.' '.$this->fundSettings->formatMoney(abs($adjustment));
            }
        }

        return [
            'id' => $item->id,
            'expense_tag' => $tag->value,
            'expense_tag_label' => $tag->label(),
            'amount' => $this->fundSettings->formatMoney($item->amount),
            'paid_on' => $item->paid_on->format('d M Y'),
            'payee_name' => $item->payee_name,
            'worker_name' => $item->worker?->name,
            'salary_breakdown' => $salaryBreakdown,
            'reference' => $item->reference,
            'notes' => $item->notes,
            'recorded_by' => $item->recordedBy?->fullName() ?? '—',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function resolveExpensePayload(array $data): array
    {
        $tag = FinanceExpenseTag::from($data['expense_tag']);

        if ($tag->requiresWorker()) {
            $worker = Worker::query()->findOrFail((int) $data['worker_id']);

            if ($worker->worker_type !== $tag->workerType()) {
                throw ValidationException::withMessages([
                    'worker_id' => [__('messages.finance_expense_worker_type_mismatch')],
                ]);
            }

            $base = round((float) $data['salary_base_amount'], 2);
            $adjustment = round((float) ($data['salary_adjustment'] ?? 0), 2);
            $amount = round((float) $data['amount'], 2);

            if (abs($amount - ($base + $adjustment)) > 0.01) {
                throw ValidationException::withMessages([
                    'amount' => [__('messages.finance_expense_amount_mismatch')],
                ]);
            }

            if ($amount < 0.01) {
                throw ValidationException::withMessages([
                    'amount' => [__('messages.finance_expense_amount_too_low')],
                ]);
            }

            return [
                'expense_tag' => $data['expense_tag'],
                'worker_id' => $worker->id,
                'amount' => $amount,
                'salary_base_amount' => $base,
                'salary_adjustment' => $adjustment,
                'salary_adjustment_note' => $data['salary_adjustment_note'] ?? null,
                'paid_on' => $data['paid_on'],
                'payee_name' => $worker->name,
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
            ];
        }

        return [
            'expense_tag' => $data['expense_tag'],
            'worker_id' => null,
            'amount' => $data['amount'],
            'salary_base_amount' => null,
            'salary_adjustment' => 0,
            'salary_adjustment_note' => null,
            'paid_on' => $data['paid_on'],
            'payee_name' => $data['payee_name'] ?? null,
            'reference' => $data['reference'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];
    }
}
