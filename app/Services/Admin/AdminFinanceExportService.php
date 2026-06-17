<?php

namespace App\Services\Admin;

use App\Enums\FinanceCollectionType;
use App\Enums\FinanceExpenseTag;
use App\Models\FinanceCollection;
use App\Models\FinanceExpense;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminFinanceExportService
{
    public function __construct(
        private readonly AdminFinanceCollectionService $collections,
        private readonly AdminFinanceExpenseService $expenses,
    ) {}

    /**
     * @param  array{collection_type?: string, main_member_id?: string, date_from?: string, date_to?: string}  $filters
     */
    public function collectionsCsv(array $filters): StreamedResponse
    {
        $rows = $this->collections->exportRows($filters);
        $filename = 'finance-collections-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                __('messages.finance_received_on'),
                __('messages.finance_collection_type'),
                __('messages.users_house'),
                __('messages.finance_main_member'),
                __('messages.finance_amount'),
                __('messages.finance_payment_mode'),
                __('messages.finance_reference'),
                __('messages.finance_notes'),
                __('messages.finance_recorded_by'),
            ]);

            foreach ($rows as $row) {
                /** @var FinanceCollection $row */
                $type = $row->collection_type instanceof FinanceCollectionType
                    ? $row->collection_type
                    : FinanceCollectionType::from((string) $row->collection_type);

                fputcsv($handle, [
                    $row->received_on->format('Y-m-d'),
                    $type->label(),
                    $row->mainMember?->houseLabel() ?? '',
                    $row->mainMember?->fullName() ?? '',
                    number_format((float) $row->amount, 2, '.', ''),
                    $row->payment_mode?->label() ?? '',
                    $row->reference ?? '',
                    $row->notes ?? '',
                    $row->recordedBy?->fullName() ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @param  array{expense_tag?: string, date_from?: string, date_to?: string}  $filters
     */
    public function expensesCsv(array $filters): StreamedResponse
    {
        $rows = $this->expenses->exportRows($filters);
        $filename = 'finance-expenses-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                __('messages.finance_paid_on'),
                __('messages.finance_expense_tag'),
                __('messages.workers_worker'),
                __('messages.finance_amount'),
                __('messages.workers_salary_base'),
                __('messages.workers_salary_adjustment'),
                __('messages.workers_salary_adjustment_note'),
                __('messages.finance_payee_name'),
                __('messages.finance_reference'),
                __('messages.finance_notes'),
                __('messages.finance_recorded_by'),
            ]);

            foreach ($rows as $row) {
                /** @var FinanceExpense $row */
                $tag = $row->expense_tag instanceof FinanceExpenseTag
                    ? $row->expense_tag
                    : FinanceExpenseTag::from((string) $row->expense_tag);

                fputcsv($handle, [
                    $row->paid_on->format('Y-m-d'),
                    $tag->label(),
                    $row->worker?->name ?? '',
                    number_format((float) $row->amount, 2, '.', ''),
                    $row->salary_base_amount !== null
                        ? number_format((float) $row->salary_base_amount, 2, '.', '')
                        : '',
                    $row->salary_base_amount !== null
                        ? number_format((float) $row->salary_adjustment, 2, '.', '')
                        : '',
                    $row->salary_adjustment_note ?? '',
                    $row->payee_name ?? '',
                    $row->reference ?? '',
                    $row->notes ?? '',
                    $row->recordedBy?->fullName() ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
