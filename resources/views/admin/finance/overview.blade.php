<x-layouts.admin :pageTitle="__('messages.finance_overview')">
    <div
        id="finance-overview-root"
        class="space-y-6"
        data-label-collections="{{ __('messages.finance_overview_chart_collections') }}"
        data-label-expenses="{{ __('messages.finance_overview_chart_expenses') }}"
        data-label-net="{{ __('messages.finance_overview_chart_net') }}"
        data-label-expense-breakdown="{{ __('messages.finance_overview_chart_expense_breakdown') }}"
    >
        <script type="application/json" id="finance-overview-chart-data">@json($charts)</script>
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#AB1E23]">{{ __('messages.finance') }}</p>
                <h2 class="mt-1 text-xl font-bold text-[#080D21]">{{ __('messages.finance_overview') }}</h2>
                <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.finance_overview_subtitle') }}</p>
            </div>

            @if(auth()->user()->can('finance_collections.create') || auth()->user()->can('finance_expenses.create'))
                <div class="flex flex-wrap gap-2">
                    @can('finance_collections.create')
                    @include('admin.finance.collections._add-modal', [
                        'collectionTypes' => $collectionTypes ?? [],
                        'mainMembers' => $mainMembers ?? [],
                        'paymentModes' => $paymentModes ?? [],
                        'maintenanceChargeLookupUrl' => $maintenanceChargeLookupUrl ?? '',
                        'defaultCollectionType' => $defaultCollectionType ?? 'clubhouse_booking',
                        'openOnLoad' => $openCollectionModal ?? false,
                        'showTrigger' => true,
                    ])
                    @endcan
                    @can('finance_expenses.create')
                    @include('admin.finance.expenses._add-modal', [
                        'expenseTags' => $expenseTags ?? [],
                        'workersGrouped' => $workersGrouped ?? [],
                        'workerSalaryLookupUrl' => $workerSalaryLookupUrl ?? '',
                        'openOnLoad' => $openExpenseModal ?? false,
                        'showTrigger' => true,
                    ])
                    @endcan
                </div>
            @endif
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
            <div class="rounded-xl border border-[#E6C280]/40 bg-gradient-to-r from-[#E6EBF4]/80 to-white px-5 py-4 text-sm text-[#0F141E]/80 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <p>
                        {{ __('messages.finance_overview_opening_line', [
                            'amount' => $formatted_opening_balance,
                            'date' => $formatted_effective_date,
                        ]) }}
                    </p>
                    <span class="inline-flex w-fit rounded-full bg-[#E6C280]/25 px-3 py-1 text-xs font-semibold text-[#080D21]">
                        {{ $filters['period_label'] }}
                    </span>
                </div>
            </div>
        @endif

        <form method="GET" action="{{ route('admin.finance.index') }}" class="rounded-xl border border-[#E6EBF4] bg-white p-4 shadow-sm sm:p-5">
            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#080D21]">{{ __('messages.finance_overview_filters') }}</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ([
                            'month' => __('messages.finance_overview_period_month_short'),
                            'last_month' => __('messages.finance_overview_period_last_month_short'),
                            'quarter' => __('messages.finance_overview_period_quarter_short'),
                            'year' => __('messages.finance_overview_period_year_short'),
                            'all' => __('messages.finance_overview_period_all_short'),
                        ] as $preset => $presetLabel)
                            <a
                                href="{{ route('admin.finance.index', ['period' => $preset]) }}"
                                @class([
                                    'rounded-lg px-3 py-1.5 text-xs font-semibold transition',
                                    'bg-[#AB1E23] text-[#E6EBF4] shadow-sm' => ($filters['period'] ?? 'year') === $preset,
                                    'border border-[#E6EBF4] bg-[#ECEAE1]/50 text-[#080D21] hover:bg-[#E6EBF4]' => ($filters['period'] ?? 'year') !== $preset,
                                ])
                            >
                                {{ $presetLabel }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-12 md:items-end">
                    <div class="md:col-span-3">
                        <x-common.input
                            type="date"
                            name="from"
                            :label="__('messages.finance_overview_date_from')"
                            :value="$filters['from']"
                        />
                    </div>
                    <div class="md:col-span-3">
                        <x-common.input
                            type="date"
                            name="to"
                            :label="__('messages.finance_overview_date_to')"
                            :value="$filters['to']"
                        />
                    </div>
                    <input type="hidden" name="period" value="custom">
                    <div class="flex flex-wrap gap-2 md:col-span-6">
                        <x-common.button type="submit" class="min-w-[8rem]">
                            {{ __('messages.finance_overview_apply_range') }}
                        </x-common.button>
                        <x-common.button type="button" variant="secondary" :href="route('admin.finance.index', ['period' => 'year'])">
                            {{ __('messages.finance_overview_reset_filters') }}
                        </x-common.button>
                    </div>
                </div>
            </div>
        </form>

        <section class="space-y-3">
            <div class="flex items-center justify-between gap-3">
                <h3 class="text-sm font-bold uppercase tracking-wide text-[#080D21]">
                    {{ __('messages.finance_overview_stats') }}
                </h3>
                <span class="text-xs text-[#0F141E]/50">{{ __('messages.finance_overview_vs_previous') }}</span>
            </div>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($fund_stats as $stat)
                    <x-admin.stat-card
                        :label="$stat['label']"
                        :value="$stat['value']"
                        :valueExact="$stat['value_exact'] ?? null"
                        :icon="$stat['icon'] ?? null"
                        :hint="$stat['hint'] ?? null"
                        :tone="$stat['tone'] ?? 'neutral'"
                        :trend="$stat['trend'] ?? null"
                        class="min-h-[8.5rem]"
                    />
                @endforeach
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-3">
            <div class="xl:col-span-2 space-y-3">
                <h3 class="text-sm font-bold uppercase tracking-wide text-[#080D21]">
                    {{ __('messages.finance_overview_chart_cashflow') }}
                </h3>
                <div class="rounded-xl border border-[#E6EBF4] bg-white p-4 shadow-sm sm:p-5">
                    <div class="h-72 sm:h-80">
                        <canvas id="finance-chart-trend"></canvas>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <h3 class="text-sm font-bold uppercase tracking-wide text-[#080D21]">
                    {{ __('messages.finance_overview_chart_collections_mix') }}
                </h3>
                <div class="rounded-xl border border-[#E6EBF4] bg-white p-4 shadow-sm sm:p-5">
                    <div class="h-72 sm:h-80">
                        <canvas id="finance-chart-collections"></canvas>
                    </div>
                </div>
            </div>
        </section>

        <section class="space-y-3">
            <h3 class="text-sm font-bold uppercase tracking-wide text-[#080D21]">
                {{ __('messages.finance_overview_chart_expense_breakdown') }}
            </h3>
            <div class="rounded-xl border border-[#E6EBF4] bg-white p-4 shadow-sm sm:p-5">
                <div class="h-80 sm:h-96">
                    <canvas id="finance-chart-expenses"></canvas>
                </div>
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
                <div class="overflow-hidden rounded-xl border border-[#E6EBF4] bg-white shadow-sm">
                    @forelse ($recent_collections as $row)
                        <div class="flex items-center justify-between gap-3 border-b border-[#E6EBF4] px-4 py-3 last:border-b-0 hover:bg-[#ECEAE1]/30">
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
                <div class="overflow-hidden rounded-xl border border-[#E6EBF4] bg-white shadow-sm">
                    @forelse ($recent_expenses as $row)
                        <div class="flex items-center justify-between gap-3 border-b border-[#E6EBF4] px-4 py-3 last:border-b-0 hover:bg-[#ECEAE1]/30">
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
