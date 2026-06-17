@props([
    'expenseTags' => [],
    'workersGrouped' => [],
    'workerSalaryLookupUrl' => '',
    'editingExpense' => null,
    'openOnLoad' => false,
])

@if ($editingExpense)
    <div
        x-data="{
            open: @js((bool) $openOnLoad),
            title: @js(__('messages.finance_expense_edit')),
        }"
    >
        <x-common.modal maxWidth="max-w-2xl">
            <form method="POST" action="{{ route('admin.finance.expenses.update') }}" class="space-y-5">
                @csrf
                @method('PUT')
                <input type="hidden" name="_finance_form" value="expense-edit">

                @if ($editingExpense->recordedBy)
                    <p class="rounded-lg border border-[#E6EBF4] bg-[#E6EBF4]/40 px-4 py-3 text-xs text-[#0F141E]/70">
                        {{ __('messages.finance_recorded_by') }}:
                        <span class="font-semibold text-[#080D21]">{{ $editingExpense->recordedBy->fullName() }}</span>
                    </p>
                @endif

                @include('admin.finance.expenses._form', [
                    'expenseTags' => $expenseTags,
                    'workersGrouped' => $workersGrouped,
                    'workerSalaryLookupUrl' => $workerSalaryLookupUrl,
                    'notesFieldId' => 'finance-expense-edit-notes',
                    'editingExpense' => $editingExpense,
                ])

                <div class="flex flex-wrap justify-end gap-3 border-t border-[#E6EBF4] pt-4">
                    <x-common.button type="button" variant="secondary" :href="route('admin.finance.expenses.cancel-edit')">
                        {{ __('messages.finance_cancel') }}
                    </x-common.button>
                    <x-common.button type="submit">
                        {{ __('messages.finance_expense_update') }}
                    </x-common.button>
                </div>
            </form>
        </x-common.modal>
    </div>
@endif
