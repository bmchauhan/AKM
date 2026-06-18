@php
    $hasActiveFilters = ($filters['status'] ?? 'outstanding') !== 'outstanding'
        || filled($filters['house'] ?? '')
        || filled($filters['name'] ?? '');
@endphp

<x-layouts.admin :pageTitle="__('messages.finance_house_ledger')">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#AB1E23]">{{ __('messages.finance') }}</p>
                <h2 class="mt-1 text-xl font-bold text-[#080D21]">{{ __('messages.finance_house_ledger') }}</h2>
                <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.finance_house_ledger_subtitle') }}</p>
            </div>

            <x-common.button variant="secondary" :href="route('admin.finance.maintenance-ledger.index')">
                {{ __('messages.finance_maintenance_ledger') }}
            </x-common.button>
        </div>

        <form method="GET" action="{{ route('admin.finance.maintenance-ledger.house') }}" class="rounded-xl border border-[#E6EBF4] bg-white p-4 sm:p-5">
            <div class="grid gap-4 md:grid-cols-12 md:items-end">
                <div class="md:col-span-3">
                    <x-common.input
                        name="house"
                        :label="__('messages.users_house_number')"
                        :value="$filters['house'] ?? ''"
                        :placeholder="__('messages.finance_ledger_filter_house')"
                    />
                </div>
                <div class="md:col-span-3">
                    <x-common.input
                        name="name"
                        :label="__('messages.finance_main_member')"
                        :value="$filters['name'] ?? ''"
                        :placeholder="__('messages.finance_house_ledger_name_placeholder')"
                    />
                </div>
                <div class="md:col-span-2">
                    <x-common.select name="status" :label="__('messages.finance_maintenance_status')" :options="$statusOptions" :value="$filters['status']" />
                </div>
                <div class="flex flex-wrap gap-2 md:col-span-4">
                    <x-common.button type="submit">{{ __('messages.finance_house_ledger_search') }}</x-common.button>
                    @if ($hasActiveFilters || $has_searched)
                        <x-common.button type="button" variant="secondary" :href="route('admin.finance.maintenance-ledger.house')">
                            {{ __('messages.users_filter_clear') }}
                        </x-common.button>
                    @endif
                </div>
            </div>
            <p class="mt-3 text-xs text-[#0F141E]/60">{{ __('messages.finance_house_ledger_search_hint') }}</p>
        </form>

        @unless ($has_searched)
            <div class="rounded-xl border border-[#E6EBF4] bg-[#E6EBF4]/40 p-8 text-center">
                <p class="font-medium text-[#080D21]">{{ __('messages.finance_house_ledger_prompt') }}</p>
                <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.finance_house_ledger_prompt_hint') }}</p>
            </div>
        @elseif ($match_count === 0)
            <div class="rounded-xl border border-[#E5989B]/40 bg-[#E5989B]/10 p-6 text-center">
                <p class="font-medium text-[#080D21]">{{ __('messages.finance_house_ledger_not_found') }}</p>
            </div>
        @elseif ($match_count > 1)
            <div class="rounded-xl border border-[#E5989B]/40 bg-[#E5989B]/10 p-6">
                <p class="font-medium text-[#080D21]">{{ __('messages.finance_house_ledger_choose_member', ['count' => $match_count]) }}</p>
                <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.finance_house_ledger_choose_member_hint') }}</p>
                <div class="mt-4 space-y-2">
                    @foreach ($match_samples as $sample)
                        <a
                            href="{{ route('admin.finance.maintenance-ledger.house', array_filter([
                                'member_id' => $sample['id'],
                                'house' => $sample['house'],
                                'status' => $filters['status'] ?? 'outstanding',
                            ])) }}"
                            class="flex items-center justify-between rounded-lg border border-[#E6EBF4] bg-white px-4 py-3 text-sm transition hover:border-[#AB1E23] hover:bg-[#E6EBF4]/40"
                        >
                            <span class="font-semibold text-[#080D21]">{{ $sample['house'] }}</span>
                            <span class="text-[#0F141E]">{{ $sample['name'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @else
            <div class="rounded-xl border border-[#E6EBF4] bg-white p-4 sm:p-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-[#0F141E]/50">{{ __('messages.finance_main_member') }}</p>
                        <p class="mt-1 text-lg font-bold text-[#080D21]">{{ $member->houseLabel() }} — {{ $member->fullName() }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-semibold uppercase text-[#AB1E23]">{{ __('messages.finance_house_ledger_outstanding') }}</p>
                        <p class="text-xl font-bold text-[#AB1E23]">{{ $summary['outstanding_total'] }}</p>
                    </div>
                </div>
                @can('finance_house_ledger.update')
                    <div class="mt-4 flex justify-end border-t border-[#E6EBF4] pt-4">
                        @include('admin.finance.maintenance-ledger._bulk-payment-modal', [
                            'member' => $member,
                            'allocationEntries' => $allocationEntries ?? [],
                            'totalOutstandingRaw' => $totalOutstandingRaw ?? 0,
                            'paymentModes' => $paymentModes ?? [],
                            'houseReturn' => $houseReturn,
                        ])
                    </div>
                @endcan
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                <div class="rounded-xl border border-[#E6EBF4] bg-[#E6EBF4]/40 p-4">
                    <p class="text-xs font-semibold uppercase text-[#0F141E]/50">{{ __('messages.finance_ledger_status_pending') }}</p>
                    <p class="mt-1 text-2xl font-bold text-[#080D21]">{{ $summary['pending'] }}</p>
                </div>
                <div class="rounded-xl border border-[#E5989B]/30 bg-[#E5989B]/10 p-4">
                    <p class="text-xs font-semibold uppercase text-[#AB1E23]">{{ __('messages.finance_ledger_status_due') }}</p>
                    <p class="mt-1 text-2xl font-bold text-[#AB1E23]">{{ $summary['due'] }}</p>
                </div>
                <div class="rounded-xl border border-[#E6C280]/40 bg-[#E6C280]/15 p-4">
                    <p class="text-xs font-semibold uppercase text-[#080D21]">{{ __('messages.finance_ledger_status_partial') }}</p>
                    <p class="mt-1 text-2xl font-bold text-[#080D21]">{{ $summary['partial'] }}</p>
                </div>
                <div class="rounded-xl border border-[#E6C280]/50 bg-[#E6C280]/25 p-4">
                    <p class="text-xs font-semibold uppercase text-[#080D21]">{{ __('messages.finance_ledger_status_paid') }}</p>
                    <p class="mt-1 text-2xl font-bold text-[#080D21]">{{ $summary['paid'] }}</p>
                </div>
                <div class="rounded-xl border border-[#E6EBF4] bg-white p-4">
                    <p class="text-xs font-semibold uppercase text-[#0F141E]/50">{{ __('messages.finance_house_ledger_months') }}</p>
                    <p class="mt-1 text-2xl font-bold text-[#080D21]">{{ $summary['months_total'] }}</p>
                    <p class="mt-1 text-xs text-[#0F141E]/60">{{ $summary['collected_total'] }} / {{ $summary['expected_total'] }}</p>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-[#E6EBF4] bg-white shadow-sm">
                <div class="hidden border-b border-[#E6EBF4] bg-[#E6EBF4]/50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-[#080D21] md:grid md:grid-cols-12 md:gap-2">
                    <div class="md:col-span-2">{{ __('messages.finance_billing_month') }}</div>
                    <div class="md:col-span-2 text-right">{{ __('messages.finance_maintenance_charge_amount') }}</div>
                    <div class="md:col-span-2 text-right">{{ __('messages.finance_amount_received') }}</div>
                    <div class="md:col-span-2">{{ __('messages.finance_maintenance_status') }}</div>
                    <div class="md:col-span-3">{{ __('messages.finance_reference') }}</div>
                    <div class="md:col-span-1 text-right">{{ __('messages.users_actions') }}</div>
                </div>

                @forelse ($entries as $entry)
                    <div class="border-b border-[#E6EBF4] px-4 py-3 last:border-b-0 md:grid md:grid-cols-12 md:items-center md:gap-2">
                        <div class="mb-1 md:col-span-2 md:mb-0">
                            <a href="{{ $entry['month_ledger_url'] }}" class="text-sm font-semibold text-[#AB1E23] hover:underline">
                                {{ $entry['billing_month_label'] }}
                            </a>
                        </div>
                        <div class="mb-1 text-right text-sm text-[#0F141E]/70 md:col-span-2 md:mb-0">{{ $entry['charge_amount'] }}</div>
                        <div class="mb-1 text-right text-sm font-semibold text-[#AB1E23] md:col-span-2 md:mb-0">{{ $entry['amount_paid'] }}</div>
                        <div class="mb-2 md:col-span-2 md:mb-0">
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $entry['status_badge'] }}">
                                {{ $entry['status_label'] }}
                            </span>
                            @if ($entry['paid_on'])
                                <p class="mt-0.5 text-xs text-[#0F141E]/50">{{ $entry['paid_on'] }}</p>
                            @endif
                        </div>
                        <div class="mb-2 text-sm text-[#0F141E]/70 md:col-span-3 md:mb-0">
                            {{ $entry['notes'] ?? '—' }}
                        </div>
                        <div class="flex items-center justify-end gap-1 md:col-span-1">
                            @include('admin.finance.maintenance-ledger._entry-actions', [
                                'entryId' => $entry['id'],
                                'houseReturn' => $houseReturn,
                                'showMarkPaid' => $entry['status'] !== 'paid',
                            ])
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-10 text-center text-sm text-[#0F141E]/60">
                        {{ __('messages.finance_house_ledger_no_results') }}
                    </div>
                @endforelse
            </div>

            @if ($entries->hasPages())
                <div class="rounded-xl border border-[#E6EBF4] bg-white px-4 py-3">
                    {{ $entries->links() }}
                </div>
            @endif
        @endunless
    </div>

    @include('admin.finance.maintenance-ledger._edit-modal', [
        'editingEntry' => $editingEntry ?? null,
        'paymentModes' => $paymentModes ?? [],
        'openOnLoad' => $openEditEntryModal ?? false,
        'cancelEditUrl' => $cancelEditUrl ?? null,
        'houseReturn' => $houseReturn ?? [],
    ])
</x-layouts.admin>
