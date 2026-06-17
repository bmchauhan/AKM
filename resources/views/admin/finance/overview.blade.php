<x-layouts.admin :pageTitle="__('messages.finance_overview')">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#AB1E23]">{{ __('messages.finance') }}</p>
                <h2 class="mt-1 text-xl font-bold text-[#080D21]">{{ __('messages.finance_overview') }}</h2>
                <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.finance_overview_subtitle') }}</p>
            </div>

            @can('finance.create')
                <div class="flex flex-wrap gap-2">
                    @include('admin.finance.collections._add-modal', [
                        'collectionTypes' => $collectionTypes,
                        'mainMembers' => $mainMembers,
                        'paymentModes' => $paymentModes,
                        'maintenanceChargeLookupUrl' => $maintenanceChargeLookupUrl ?? '',
                        'defaultCollectionType' => $defaultCollectionType ?? 'clubhouse_booking',
                        'openOnLoad' => $openCollectionModal ?? false,
                        'showTrigger' => true,
                    ])
                    @include('admin.finance.expenses._add-modal', [
                        'expenseTags' => $expenseTags,
                        'workersGrouped' => $workersGrouped ?? [],
                        'workerSalaryLookupUrl' => $workerSalaryLookupUrl ?? '',
                        'openOnLoad' => $openExpenseModal ?? false,
                        'showTrigger' => true,
                    ])
                </div>
            @endcan
        </div>

        @if (! $is_configured)
            <div class="rounded-xl border border-[#E5989B]/40 bg-[#E5989B]/10 p-5 shadow-sm">
                <p class="text-sm font-medium text-[#080D21]">{{ __('messages.finance_fund_setting_not_configured') }}</p>
                <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.finance_overview_not_configured_hint') }}</p>
                @can('super-admin')
                    <div class="mt-4">
                        <x-common.button :href="route('admin.finance.fund-setting.show')">
                            {{ __('messages.finance_fund_setting_configure') }}
                        </x-common.button>
                    </div>
                @endcan
            </div>
        @else
            <div class="rounded-xl border border-[#E6C280]/40 bg-[#E6EBF4]/50 px-5 py-4 text-sm text-[#0F141E]/80">
                {{ __('messages.finance_overview_opening_line', [
                    'amount' => $formatted_opening_balance,
                    'date' => $formatted_effective_date,
                ]) }}
            </div>
        @endif

        <section class="space-y-3">
            <h3 class="text-sm font-bold uppercase tracking-wide text-[#080D21]">
                {{ __('messages.finance_overview_stats') }}
            </h3>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5">
                @foreach ($fund_stats as $stat)
                    <x-admin.stat-card
                        :label="$stat['label']"
                        :value="$stat['value']"
                        :hint="$stat['hint'] ?? null"
                    />
                @endforeach
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-sm font-bold uppercase tracking-wide text-[#080D21]">
                        {{ __('messages.finance_overview_recent_collections') }}
                    </h3>
                    <a href="{{ route('admin.finance.collections.index') }}" class="text-xs font-semibold text-[#AB1E23] hover:text-[#080D21]">
                        {{ __('messages.finance_view_all') }}
                    </a>
                </div>
                <div class="overflow-hidden rounded-xl border border-[#E6EBF4] bg-white">
                    @forelse ($recent_collections as $row)
                        <div class="flex items-center justify-between gap-3 border-b border-[#E6EBF4] px-4 py-3 last:border-b-0">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-[#080D21]">{{ $row['type_label'] }}</p>
                                <p class="mt-0.5 truncate text-xs text-[#0F141E]/60">
                                    {{ $row['date'] }}
                                    @if ($row['house'] !== '—')
                                        · {{ $row['house'] }}
                                    @endif
                                    @if ($row['main_member'])
                                        · {{ $row['main_member'] }}
                                    @endif
                                </p>
                            </div>
                            <p class="shrink-0 text-sm font-semibold text-[#080D21]">{{ $row['amount'] }}</p>
                        </div>
                    @empty
                        <p class="px-4 py-8 text-center text-sm text-[#0F141E]/60">{{ __('messages.finance_collections_empty') }}</p>
                    @endforelse
                </div>
            </section>

            <section class="space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-sm font-bold uppercase tracking-wide text-[#080D21]">
                        {{ __('messages.finance_overview_recent_expenses') }}
                    </h3>
                    <a href="{{ route('admin.finance.expenses.index') }}" class="text-xs font-semibold text-[#AB1E23] hover:text-[#080D21]">
                        {{ __('messages.finance_view_all') }}
                    </a>
                </div>
                <div class="overflow-hidden rounded-xl border border-[#E6EBF4] bg-white">
                    @forelse ($recent_expenses as $row)
                        <div class="flex items-center justify-between gap-3 border-b border-[#E6EBF4] px-4 py-3 last:border-b-0">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-[#080D21]">{{ $row['tag_label'] }}</p>
                                <p class="mt-0.5 truncate text-xs text-[#0F141E]/60">
                                    {{ $row['date'] }} · {{ $row['payee_name'] }}
                                </p>
                            </div>
                            <p class="shrink-0 text-sm font-semibold text-[#AB1E23]">{{ $row['amount'] }}</p>
                        </div>
                    @empty
                        <p class="px-4 py-8 text-center text-sm text-[#0F141E]/60">{{ __('messages.finance_expenses_empty') }}</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-layouts.admin>
