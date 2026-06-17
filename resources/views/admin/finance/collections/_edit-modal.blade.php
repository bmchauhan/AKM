@props([
    'collectionTypes' => [],
    'mainMembers' => [],
    'paymentModes' => [],
    'maintenanceChargeLookupUrl' => '',
    'editingCollection' => null,
    'openOnLoad' => false,
])

@if ($editingCollection)
    <div
        x-data="{
            open: @js((bool) $openOnLoad),
            title: @js(__('messages.finance_collection_edit')),
        }"
    >
        <x-common.modal maxWidth="max-w-2xl">
            <form method="POST" action="{{ route('admin.finance.collections.update') }}" class="space-y-5">
                @csrf
                @method('PUT')
                <input type="hidden" name="_finance_form" value="collection-edit">

                @if ($editingCollection->recordedBy)
                    <p class="rounded-lg border border-[#E6EBF4] bg-[#E6EBF4]/40 px-4 py-3 text-xs text-[#0F141E]/70">
                        {{ __('messages.finance_recorded_by') }}:
                        <span class="font-semibold text-[#080D21]">{{ $editingCollection->recordedBy->fullName() }}</span>
                    </p>
                @endif

                @include('admin.finance.collections._form', [
                    'collectionTypes' => $collectionTypes,
                    'mainMembers' => $mainMembers,
                    'paymentModes' => $paymentModes,
                    'maintenanceChargeLookupUrl' => $maintenanceChargeLookupUrl,
                    'defaultCollectionType' => $editingCollection->collection_type?->value ?? 'clubhouse_booking',
                    'notesFieldId' => 'finance-collection-edit-notes',
                    'editingCollection' => $editingCollection,
                ])

                <div class="flex flex-wrap justify-end gap-3 border-t border-[#E6EBF4] pt-4">
                    <x-common.button type="button" variant="secondary" :href="route('admin.finance.collections.cancel-edit')">
                        {{ __('messages.finance_cancel') }}
                    </x-common.button>
                    <x-common.button type="submit">
                        {{ __('messages.finance_collection_update') }}
                    </x-common.button>
                </div>
            </form>
        </x-common.modal>
    </div>
@endif
