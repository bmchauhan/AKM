@php
    $hasActiveFilters = collect($filters)->filter()->isNotEmpty();
    $canGoNext = $nextMonth <= now()->format('Y-m');
@endphp

<x-layouts.admin :pageTitle="__('messages.finance_maintenance_ledger')">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#AB1E23]">{{ __('messages.finance') }}</p>
                <h2 class="mt-1 text-xl font-bold text-[#080D21]">{{ __('messages.finance_maintenance_ledger') }}</h2>
                <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.finance_maintenance_ledger_subtitle') }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <x-common.button variant="secondary" :href="route('admin.finance.maintenance-ledger.house')">
                    {{ __('messages.finance_house_ledger') }}
                </x-common.button>
                <x-common.button variant="secondary" :href="route('admin.finance.maintenance-ledger.export', array_filter(['month' => $billingMonth, ...$filters]))">
                    {{ __('messages.finance_export_csv') }}
                </x-common.button>

                @can('finance_maintenance_ledger.update')
                    <form method="POST" action="{{ route('admin.finance.maintenance-ledger.sync-missing') }}" class="inline">
                        @csrf
                        <input type="hidden" name="month" value="{{ $billingMonth }}">
                        <x-common.button type="submit" variant="secondary">
                            {{ __('messages.finance_ledger_sync_missing') }}
                        </x-common.button>
                    </form>
                @endcan
            </div>
        </div>

        <div class="flex flex-col gap-4 rounded-xl border border-[#E6EBF4] bg-white p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
            <div class="flex items-center gap-3">
                <x-common.button variant="secondary" :href="route('admin.finance.maintenance-ledger.index', array_filter(['month' => $prevMonth, ...$filters]))">
                    ←
                </x-common.button>
                <div class="text-center">
                    <p class="text-lg font-bold text-[#080D21]">{{ $billingMonthLabel }}</p>
                    @if ($chargeAmount)
                        <p class="text-xs text-[#0F141E]/60">{{ __('messages.finance_ledger_charge_per_house', ['amount' => $chargeAmount]) }}</p>
                    @endif
                </div>
                @if ($canGoNext)
                    <x-common.button variant="secondary" :href="route('admin.finance.maintenance-ledger.index', array_filter(['month' => $nextMonth, ...$filters]))">
                        →
                    </x-common.button>
                @else
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-[#E6EBF4]/50 text-[#0F141E]/30">→</span>
                @endif
            </div>

            @if ($isCurrentMonth)
                <span class="inline-flex rounded-full bg-[#E6C280]/35 px-3 py-1 text-xs font-semibold text-[#080D21]">
                    {{ __('messages.finance_ledger_current_month') }}
                </span>
            @endif
        </div>

        @if ($hasEntries)
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
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
                <div class="rounded-xl border border-[#E6EBF4] bg-white p-4 lg:col-span-2">
                    <p class="text-xs font-semibold uppercase text-[#0F141E]/50">{{ __('messages.finance_ledger_collection_progress') }}</p>
                    <p class="mt-1 text-lg font-bold text-[#AB1E23]">{{ $summary['collected_total'] }} <span class="text-sm font-medium text-[#0F141E]/60">/ {{ $summary['expected_total'] }}</span></p>
                    <p class="mt-1 text-xs text-[#0F141E]/60">{{ __('messages.finance_ledger_houses_total', ['count' => $summary['total_houses']]) }}</p>
                </div>
            </div>
        @endif

        @unless ($hasEntries)
            <div class="rounded-xl border border-[#E5989B]/40 bg-[#E5989B]/10 p-6 text-center">
                <p class="font-medium text-[#080D21]">{{ __('messages.finance_ledger_empty_month') }}</p>
                <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.finance_ledger_empty_month_hint') }}</p>
                @can('finance_maintenance_ledger.update')
                    <form method="POST" action="{{ route('admin.finance.maintenance-ledger.generate') }}" class="mt-4 inline-block">
                        @csrf
                        <input type="hidden" name="month" value="{{ $billingMonth }}">
                        <x-common.button type="submit">{{ __('messages.finance_ledger_generate_month') }}</x-common.button>
                    </form>
                @endcan
            </div>
        @else
            <form method="GET" action="{{ route('admin.finance.maintenance-ledger.index') }}" class="rounded-xl border border-[#E6EBF4] bg-white p-4 sm:p-5">
                <input type="hidden" name="month" value="{{ $billingMonth }}">
                <div class="grid gap-4 md:grid-cols-12 md:items-end">
                    <div class="md:col-span-3">
                        <x-common.select name="status" :label="__('messages.finance_maintenance_status')" :options="$statusOptions" :value="$filters['status']">
                            <option value="">{{ __('messages.users_filter_all') }}</option>
                        </x-common.select>
                    </div>
                    <div class="md:col-span-2">
                        <x-common.input name="house" :label="__('messages.users_house_number')" :value="$filters['house']" :placeholder="__('messages.finance_ledger_filter_house')" />
                    </div>
                    <div class="md:col-span-3">
                        <x-common.input name="name" :label="__('messages.finance_main_member')" :value="$filters['name']" :placeholder="__('messages.users_filter_name_placeholder')" />
                    </div>
                    <div class="flex flex-wrap gap-2 md:col-span-4">
                        <x-common.button type="submit">{{ __('messages.users_filter_apply') }}</x-common.button>
                        @if ($hasActiveFilters)
                            <x-common.button type="button" variant="secondary" :href="route('admin.finance.maintenance-ledger.index', ['month' => $billingMonth])">
                                {{ __('messages.users_filter_clear') }}
                            </x-common.button>
                        @endif
                    </div>
                </div>
            </form>

            <div class="overflow-hidden rounded-xl border border-[#E6EBF4] bg-white shadow-sm">
                <div class="hidden border-b border-[#E6EBF4] bg-[#E6EBF4]/50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-[#080D21] md:grid md:grid-cols-12 md:gap-2">
                    <div class="md:col-span-2">{{ __('messages.users_house') }}</div>
                    <div class="md:col-span-3">{{ __('messages.finance_main_member') }}</div>
                    <div class="md:col-span-2 text-right">{{ __('messages.finance_maintenance_charge_amount') }}</div>
                    <div class="md:col-span-2 text-right">{{ __('messages.finance_amount_received') }}</div>
                    <div class="md:col-span-2">{{ __('messages.finance_maintenance_status') }}</div>
                    <div class="md:col-span-1 text-right">{{ __('messages.users_actions') }}</div>
                </div>

                @forelse ($entries as $entry)
                    <div class="border-b border-[#E6EBF4] px-4 py-3 last:border-b-0 md:grid md:grid-cols-12 md:items-center md:gap-2">
                        <div class="mb-1 text-sm font-semibold text-[#080D21] md:col-span-2 md:mb-0">{{ $entry['house'] }}</div>
                        <div class="mb-1 text-sm text-[#0F141E] md:col-span-3 md:mb-0">{{ $entry['member_name'] }}</div>
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
                        <div class="flex items-center justify-end gap-1 md:col-span-1">
                            @include('admin.finance.maintenance-ledger._entry-actions', [
                                'entryId' => $entry['id'],
                                'showMarkPaid' => $entry['status'] !== 'paid',
                            ])
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-10 text-center text-sm text-[#0F141E]/60">
                        {{ __('messages.finance_ledger_no_filter_results') }}
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
    ])
</x-layouts.admin>
