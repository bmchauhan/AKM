<?php

use App\Enums\FinanceExpenseTag;
use App\Enums\FinancePaymentReceiptKind;
use App\Models\FinanceCollection;
use App\Models\FinanceExpense;
use App\Models\FinancePaymentReceipt;
use App\Models\FinancePaymentReceiptLine;
use App\Models\MaintenanceMonthlyEntry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('finance_payment_receipt_lines')) {
            Schema::drop('finance_payment_receipt_lines');
        }

        if (Schema::hasTable('finance_payment_receipts')) {
            Schema::drop('finance_payment_receipts');
        }

        Schema::create('finance_payment_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number', 40)->unique();
            $table->string('receipt_kind', 30);
            $table->unsignedBigInteger('main_member_id')->nullable();
            $table->unsignedBigInteger('worker_id')->nullable();
            $table->unsignedBigInteger('finance_collection_id')->nullable();
            $table->unsignedBigInteger('finance_expense_id')->nullable();
            $table->date('paid_on');
            $table->string('payment_mode', 30)->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('total_amount', 12, 2);
            $table->unsignedBigInteger('recorded_by_user_id')->nullable();
            $table->timestamps();

            $table->foreign('main_member_id', 'fpr_member_fk')
                ->references('id')->on('users')->nullOnDelete();
            $table->foreign('worker_id', 'fpr_worker_fk')
                ->references('id')->on('workers')->nullOnDelete();
            $table->foreign('finance_collection_id', 'fpr_collection_fk')
                ->references('id')->on('finance_collections')->nullOnDelete();
            $table->foreign('finance_expense_id', 'fpr_expense_fk')
                ->references('id')->on('finance_expenses')->nullOnDelete();
            $table->foreign('recorded_by_user_id', 'fpr_recorded_by_fk')
                ->references('id')->on('users')->nullOnDelete();

            $table->index(['main_member_id', 'paid_on'], 'finance_rcpt_member_paid_idx');
            $table->index(['worker_id', 'paid_on'], 'finance_rcpt_worker_paid_idx');
        });

        Schema::create('finance_payment_receipt_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('finance_payment_receipt_id');
            $table->unsignedBigInteger('maintenance_monthly_entry_id')->nullable();
            $table->date('billing_month')->nullable();
            $table->string('description')->nullable();
            $table->decimal('charge_amount', 12, 2)->nullable();
            $table->decimal('amount_applied', 12, 2);
            $table->string('line_status', 20)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('finance_payment_receipt_id', 'fprl_receipt_fk')
                ->references('id')->on('finance_payment_receipts')->cascadeOnDelete();
            $table->foreign('maintenance_monthly_entry_id', 'fprl_maint_entry_fk')
                ->references('id')->on('maintenance_monthly_entries')->nullOnDelete();
        });

        $this->backfillExistingPayments();
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_payment_receipt_lines');
        Schema::dropIfExists('finance_payment_receipts');
    }

    private function backfillExistingPayments(): void
    {
        $sequence = 0;

        MaintenanceMonthlyEntry::query()
            ->where('amount_paid', '>', 0)
            ->whereNotNull('paid_on')
            ->orderBy('paid_on')
            ->orderBy('id')
            ->get()
            ->groupBy(fn (MaintenanceMonthlyEntry $entry) => implode('|', [
                $entry->main_member_id,
                $entry->paid_on->format('Y-m-d'),
                $entry->reference ?? '',
                $entry->payment_mode?->value ?? $entry->payment_mode ?? '',
            ]))
            ->each(function ($group) use (&$sequence): void {
                /** @var \Illuminate\Support\Collection<int, MaintenanceMonthlyEntry> $group */
                $first = $group->first();
                if (! $first) {
                    return;
                }

                $sequence++;
                $receipt = FinancePaymentReceipt::query()->create([
                    'receipt_number' => $this->legacyReceiptNumber($sequence),
                    'receipt_kind' => FinancePaymentReceiptKind::Maintenance->value,
                    'main_member_id' => $first->main_member_id,
                    'paid_on' => $first->paid_on,
                    'payment_mode' => $first->payment_mode?->value ?? $first->payment_mode,
                    'reference' => $first->reference,
                    'notes' => $first->notes,
                    'total_amount' => 0,
                    'recorded_by_user_id' => $first->recorded_by_user_id,
                    'created_at' => $first->updated_at ?? $first->created_at,
                    'updated_at' => $first->updated_at ?? $first->created_at,
                ]);

                $sort = 0;
                $lineTotal = 0.0;
                foreach ($group->sortBy('billing_month') as $entry) {
                    $applied = (float) $entry->amount_paid;
                    $lineTotal += $applied;
                    FinancePaymentReceiptLine::query()->create([
                        'finance_payment_receipt_id' => $receipt->id,
                        'maintenance_monthly_entry_id' => $entry->id,
                        'billing_month' => $entry->billing_month,
                        'description' => $entry->billing_month->format('F Y'),
                        'charge_amount' => $entry->charge_amount,
                        'amount_applied' => $applied,
                        'line_status' => $entry->status?->value ?? $entry->status,
                        'sort_order' => $sort++,
                    ]);
                }

                $receipt->update(['total_amount' => round($lineTotal, 2)]);
            });

        FinanceCollection::query()
            ->orderBy('received_on')
            ->orderBy('id')
            ->each(function (FinanceCollection $collection) use (&$sequence): void {
                $sequence++;

                $receipt = FinancePaymentReceipt::query()->create([
                    'receipt_number' => $this->legacyReceiptNumber($sequence),
                    'receipt_kind' => FinancePaymentReceiptKind::Collection->value,
                    'main_member_id' => $collection->main_member_id,
                    'finance_collection_id' => $collection->id,
                    'paid_on' => $collection->received_on,
                    'payment_mode' => $collection->payment_mode?->value ?? $collection->payment_mode,
                    'reference' => $collection->reference,
                    'notes' => $collection->notes,
                    'total_amount' => $collection->amount,
                    'recorded_by_user_id' => $collection->recorded_by_user_id,
                    'created_at' => $collection->created_at,
                    'updated_at' => $collection->updated_at,
                ]);

                $type = $collection->collection_type;

                FinancePaymentReceiptLine::query()->create([
                    'finance_payment_receipt_id' => $receipt->id,
                    'description' => $type->label(),
                    'amount_applied' => $collection->amount,
                    'sort_order' => 0,
                ]);
            });

        FinanceExpense::query()
            ->whereNotNull('worker_id')
            ->orderBy('paid_on')
            ->orderBy('id')
            ->each(function (FinanceExpense $expense) use (&$sequence): void {
                $sequence++;

                $receipt = FinancePaymentReceipt::query()->create([
                    'receipt_number' => $this->legacyReceiptNumber($sequence),
                    'receipt_kind' => FinancePaymentReceiptKind::WorkerSalary->value,
                    'worker_id' => $expense->worker_id,
                    'finance_expense_id' => $expense->id,
                    'paid_on' => $expense->paid_on,
                    'reference' => $expense->reference,
                    'notes' => $expense->salary_adjustment_note ?: $expense->notes,
                    'total_amount' => $expense->amount,
                    'recorded_by_user_id' => $expense->recorded_by_user_id,
                    'created_at' => $expense->created_at,
                    'updated_at' => $expense->updated_at,
                ]);

                $sort = 0;
                if ($expense->salary_base_amount !== null) {
                    FinancePaymentReceiptLine::query()->create([
                        'finance_payment_receipt_id' => $receipt->id,
                        'description' => __('messages.workers_salary_base'),
                        'amount_applied' => $expense->salary_base_amount,
                        'sort_order' => $sort++,
                    ]);
                }

                if ((float) $expense->salary_adjustment !== 0.0) {
                    FinancePaymentReceiptLine::query()->create([
                        'finance_payment_receipt_id' => $receipt->id,
                        'description' => __('messages.workers_salary_adjustment'),
                        'amount_applied' => $expense->salary_adjustment,
                        'sort_order' => $sort++,
                    ]);
                }

                if ($sort === 0) {
                    $tag = $expense->expense_tag instanceof FinanceExpenseTag
                        ? $expense->expense_tag
                        : FinanceExpenseTag::from((string) $expense->expense_tag);

                    FinancePaymentReceiptLine::query()->create([
                        'finance_payment_receipt_id' => $receipt->id,
                        'description' => $tag->label(),
                        'amount_applied' => $expense->amount,
                        'sort_order' => 0,
                    ]);
                }
            });
    }

    private function legacyReceiptNumber(int $sequence): string
    {
        return sprintf('AKM/RCP/%s/%05d', now()->format('Y'), $sequence);
    }
};
