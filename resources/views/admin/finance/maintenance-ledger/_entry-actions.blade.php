@props([
    'entryId' => null,
    'houseReturn' => [],
    'showMarkPaid' => true,
])

@can('finance.update')
    @if ($entryId)
        @if ($showMarkPaid)
            <form method="POST" action="{{ route('admin.finance.maintenance-ledger.mark-paid') }}" class="inline-flex">
            @csrf
            <input type="hidden" name="entry_id" value="{{ $entryId }}">
            @foreach ($houseReturn as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endforeach
            <x-common.icon-action type="submit" :title="__('messages.finance_ledger_mark_paid')" variant="accent">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            </x-common.icon-action>
        </form>
        @endif
        <form method="POST" action="{{ route('admin.finance.maintenance-ledger.open-edit') }}" class="inline-flex">
            @csrf
            <input type="hidden" name="entry_id" value="{{ $entryId }}">
            @foreach ($houseReturn as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endforeach
            <x-common.icon-action type="submit" :title="__('messages.finance_ledger_edit_entry')">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
            </x-common.icon-action>
        </form>
    @endif
@endcan
