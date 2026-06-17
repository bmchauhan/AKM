@props([
    'collectionTypes' => [],
    'mainMembers' => [],
    'paymentModes' => [],
    'maintenanceChargeLookupUrl' => '',
    'defaultCollectionType' => 'clubhouse_booking',
    'openOnLoad' => false,
    'showTrigger' => true,
])

<div
    x-data="{
        open: @js((bool) $openOnLoad),
        title: @js(__('messages.finance_collection_add')),
    }"
>
    @if ($showTrigger)
        @can('finance.create')
            <x-common.button type="button" @click="open = true">
                {{ __('messages.finance_collection_add') }}
            </x-common.button>
        @endcan
    @endif

    <x-common.modal maxWidth="max-w-2xl">
        <form method="POST" action="{{ route('admin.finance.collections.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="_finance_form" value="collection">

            @include('admin.finance.collections._form', [
                'collectionTypes' => $collectionTypes,
                'mainMembers' => $mainMembers,
                'paymentModes' => $paymentModes,
                'maintenanceChargeLookupUrl' => $maintenanceChargeLookupUrl,
                'defaultCollectionType' => $defaultCollectionType,
                'notesFieldId' => 'finance-collection-notes',
            ])

            <div class="flex flex-wrap justify-end gap-3 border-t border-[#E6EBF4] pt-4">
                <x-common.button type="button" variant="secondary" @click="open = false">
                    {{ __('messages.finance_cancel') }}
                </x-common.button>
                <x-common.button type="submit">
                    {{ __('messages.finance_collection_save') }}
                </x-common.button>
            </div>
        </form>
    </x-common.modal>
</div>
