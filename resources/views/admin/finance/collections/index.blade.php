@php
    $hasActiveFilters = collect($filters)->filter()->isNotEmpty();
@endphp

<x-layouts.admin :pageTitle="__('messages.finance_collections')">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#AB1E23]">{{ __('messages.finance') }}</p>
                <h2 class="mt-1 text-xl font-bold text-[#080D21]">{{ __('messages.finance_collections') }}</h2>
                <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.finance_collections_other_subtitle') }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <x-common.button variant="secondary" :href="route('admin.finance.maintenance-ledger.index')">
                    {{ __('messages.finance_maintenance_ledger') }}
                </x-common.button>
                <x-common.button variant="secondary" :href="route('admin.finance.collections.export', array_filter($filters))">
                    {{ __('messages.finance_export_csv') }}
                </x-common.button>

                @can('finance_collections.create')
                    @include('admin.finance.collections._add-modal', [
                        'collectionTypes' => $collectionTypes,
                        'mainMembers' => $mainMembers,
                        'paymentModes' => $paymentModes,
                        'maintenanceChargeLookupUrl' => $maintenanceChargeLookupUrl ?? '',
                        'defaultCollectionType' => $defaultCollectionType,
                        'openOnLoad' => $openCollectionModal ?? false,
                        'showTrigger' => true,
                    ])
                @endcan
            </div>
        </div>

        <form method="GET" action="{{ route('admin.finance.collections.index') }}" class="rounded-xl border border-[#E6EBF4] bg-white p-4 sm:p-5">
            <div class="grid gap-4 md:grid-cols-12 md:items-end">
                <div class="md:col-span-3">
                    <x-common.select
                        name="collection_type"
                        :label="__('messages.finance_collection_type')"
                        :options="$collectionTypes"
                        :value="$filters['collection_type']"
                    >
                        <option value="">{{ __('messages.users_filter_all') }}</option>
                    </x-common.select>
                </div>
                <div class="md:col-span-3">
                    <x-common.select
                        name="main_member_id"
                        :label="__('messages.finance_main_member')"
                        :options="collect($mainMembers)->map(fn ($m) => ['value' => $m['value'], 'label' => $m['label']])->all()"
                        :value="$filters['main_member_id']"
                    >
                        <option value="">{{ __('messages.users_filter_all') }}</option>
                    </x-common.select>
                </div>
                <div class="md:col-span-2">
                    <x-common.input
                        type="date"
                        name="date_from"
                        :label="__('messages.finance_date_from')"
                        :value="$filters['date_from']"
                    />
                </div>
                <div class="md:col-span-2">
                    <x-common.input
                        type="date"
                        name="date_to"
                        :label="__('messages.finance_date_to')"
                        :value="$filters['date_to']"
                    />
                </div>
                <div class="flex flex-wrap gap-2 md:col-span-2">
                    <x-common.button type="submit" class="min-w-[7rem]">
                        {{ __('messages.users_filter_apply') }}
                    </x-common.button>
                    @if ($hasActiveFilters)
                        <x-common.button type="button" variant="secondary" :href="route('admin.finance.collections.index')">
                            {{ __('messages.users_filter_clear') }}
                        </x-common.button>
                    @endif
                </div>
            </div>
        </form>

        <div class="overflow-hidden rounded-xl border border-[#E6EBF4] bg-white">
            <div class="hidden border-b border-[#E6EBF4] bg-[#E6EBF4]/50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-[#080D21] md:grid md:grid-cols-12 md:gap-2">
                <div class="md:col-span-2">{{ __('messages.finance_received_on') }}</div>
                <div class="md:col-span-2">{{ __('messages.finance_collection_type') }}</div>
                <div class="md:col-span-2">{{ __('messages.users_house') }}</div>
                <div class="md:col-span-2">{{ __('messages.finance_main_member') }}</div>
                <div class="md:col-span-1 text-right">{{ __('messages.finance_amount') }}</div>
                <div class="md:col-span-2">{{ __('messages.finance_recorded_by') }}</div>
                <div class="md:col-span-1 text-right">{{ __('messages.users_actions') }}</div>
            </div>

            @forelse ($collections as $collection)
                <div class="border-b border-[#E6EBF4] px-4 py-3 last:border-b-0 md:grid md:grid-cols-12 md:items-center md:gap-2">
                    <div class="mb-1 text-sm text-[#0F141E] md:col-span-2 md:mb-0">{{ $collection['received_on'] }}</div>
                    <div class="mb-1 md:col-span-2 md:mb-0">
                        <span class="inline-flex rounded-full bg-[#E6EBF4] px-2.5 py-0.5 text-xs font-medium text-[#080D21]">
                            {{ $collection['collection_type_label'] }}
                        </span>
                    </div>
                    <div class="mb-1 text-sm text-[#0F141E] md:col-span-2 md:mb-0">{{ $collection['house'] ?? '—' }}</div>
                    <div class="mb-1 text-sm text-[#0F141E] md:col-span-2 md:mb-0">{{ $collection['main_member_name'] ?? '—' }}</div>
                    <div class="mb-1 text-right text-sm font-semibold text-[#080D21] md:col-span-1 md:mb-0">{{ $collection['amount'] }}</div>
                    <div class="mb-1 text-sm text-[#0F141E]/70 md:col-span-2 md:mb-0">
                        {{ $collection['recorded_by'] }}
                        @if ($collection['notes'])
                            <p class="mt-0.5 text-xs text-[#0F141E]/50">{{ $collection['notes'] }}</p>
                        @endif
                    </div>
                    <div class="flex items-center justify-end gap-1 md:col-span-1">
                        @can('finance_collections.update')
                            <form method="POST" action="{{ route('admin.finance.collections.open-edit') }}" class="inline-flex">
                                @csrf
                                <input type="hidden" name="collection_id" value="{{ $collection['id'] }}">
                                <x-common.icon-action type="submit" :title="__('messages.finance_edit')">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </x-common.icon-action>
                            </form>
                        @endcan
                        @can('finance_collections.delete')
                            <x-common.delete-form
                                :action="route('admin.finance.collections.destroy')"
                                :message="__('messages.finance_collection_delete_confirm')"
                            >
                                <input type="hidden" name="collection_id" value="{{ $collection['id'] }}">
                                <x-common.icon-action type="submit" :title="__('messages.finance_delete')">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </x-common.icon-action>
                            </x-common.delete-form>
                        @endcan
                    </div>
                </div>
            @empty
                <div class="px-4 py-10 text-center text-sm text-[#0F141E]/60">
                    {{ __('messages.finance_collections_empty') }}
                </div>
            @endforelse
        </div>

        @if ($collections->hasPages())
            <div class="rounded-xl border border-[#E6EBF4] bg-white px-4 py-3">
                {{ $collections->links() }}
            </div>
        @endif
    </div>

    @include('admin.finance.collections._edit-modal', [
        'collectionTypes' => $editCollectionTypes ?? $collectionTypes,
        'mainMembers' => $mainMembers,
        'paymentModes' => $paymentModes,
        'maintenanceChargeLookupUrl' => $maintenanceChargeLookupUrl ?? '',
        'editingCollection' => $editingCollection ?? null,
        'openOnLoad' => $openEditCollectionModal ?? false,
    ])
</x-layouts.admin>
