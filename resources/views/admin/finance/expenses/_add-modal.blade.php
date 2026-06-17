@props([
    'expenseTags' => [],
    'workersGrouped' => [],
    'workerSalaryLookupUrl' => '',
    'openOnLoad' => false,
    'showTrigger' => true,
])

<div
    x-data="{
        open: @js((bool) $openOnLoad),
        title: @js(__('messages.finance_expense_add')),
    }"
>
    @if ($showTrigger)
        @can('finance.create')
            <x-common.button type="button" @click="open = true">
                {{ __('messages.finance_expense_add') }}
            </x-common.button>
        @endcan
    @endif

    <x-common.modal maxWidth="max-w-2xl">
        <form method="POST" action="{{ route('admin.finance.expenses.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="_finance_form" value="expense">

            @include('admin.finance.expenses._form', [
                'expenseTags' => $expenseTags,
                'workersGrouped' => $workersGrouped,
                'workerSalaryLookupUrl' => $workerSalaryLookupUrl,
                'notesFieldId' => 'finance-expense-notes',
            ])

            <div class="flex flex-wrap justify-end gap-3 border-t border-[#E6EBF4] pt-4">
                <x-common.button type="button" variant="secondary" @click="open = false">
                    {{ __('messages.finance_cancel') }}
                </x-common.button>
                <x-common.button type="submit">
                    {{ __('messages.finance_expense_save') }}
                </x-common.button>
            </div>
        </form>
    </x-common.modal>
</div>
