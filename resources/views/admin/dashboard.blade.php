<x-layouts.admin :pageTitle="__('messages.dashboard')">
    <div class="space-y-6">
        <div class="rounded-xl border border-[#E6EBF4] bg-white p-5 shadow-sm sm:p-6">
            <p class="text-lg font-semibold text-[#080D21]">
                {{ __('messages.dashboard_welcome', ['name' => auth()->user()->fullName()]) }}
            </p>
            <p class="mt-1 text-sm text-[#0F141E]/70">
                {{ __('messages.dashboard_role', ['role' => auth()->user()->roleLabel()]) }}
            </p>
            @if (auth()->user()->houseLabel() && (auth()->user()->isMainMember() || auth()->user()->isFamilyMember() || auth()->user()->isRentalMember()))
                <p class="mt-1 text-sm text-[#0F141E]/50">{{ auth()->user()->houseLabel() }}</p>
            @endif
        </div>

        @if (! empty($societyStats))
            <section class="space-y-3">
                <h2 class="text-sm font-bold uppercase tracking-wide text-[#080D21]">
                    {{ __('messages.dashboard_section_society') }}
                </h2>
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5">
                    @foreach ($societyStats as $stat)
                        <x-admin.stat-card
                            :label="$stat['label']"
                            :value="$stat['value']"
                            :hint="$stat['hint'] ?? null"
                        />
                    @endforeach
                </div>
            </section>
        @endif

        @if (! empty($householdStats))
            <section class="space-y-3">
                <h2 class="text-sm font-bold uppercase tracking-wide text-[#080D21]">
                    {{ __('messages.dashboard_section_household') }}
                </h2>
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($householdStats as $stat)
                        <x-admin.stat-card
                            :label="$stat['label']"
                            :value="$stat['value']"
                            :hint="$stat['hint'] ?? null"
                        />
                    @endforeach
                </div>
                @if ($showMyPaymentsLink ?? false)
                    <div class="flex justify-end">
                        <a href="{{ route('admin.finance.my-payments.index') }}" class="text-sm font-semibold text-[#AB1E23] hover:text-[#080D21]">
                            {{ __('messages.finance_my_payments_view') }} →
                        </a>
                    </div>
                @endif
            </section>
        @endif

        @if (! empty($financeStats))
            <section class="space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-[#080D21]">
                        {{ __('messages.dashboard_section_finance') }}
                    </h2>
                    <a href="{{ route('admin.finance.index') }}" class="text-xs font-semibold text-[#AB1E23] hover:text-[#080D21]">
                        {{ __('messages.finance_view_overview') }}
                    </a>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5">
                    @foreach ($financeStats as $stat)
                        <x-admin.stat-card
                            :label="$stat['label']"
                            :value="$stat['value']"
                            :hint="$stat['hint'] ?? null"
                        />
                    @endforeach
                </div>
            </section>
        @endif

        @if ($residentCard)
            <section class="space-y-3">
                <h2 class="text-sm font-bold uppercase tracking-wide text-[#080D21]">
                    {{ __('messages.dashboard_section_resident') }}
                </h2>
                <div class="rounded-xl border border-[#E6EBF4] bg-white p-5 shadow-sm sm:p-6">
                    <dl class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-[#0F141E]/50">{{ __('messages.members_type') }}</dt>
                            <dd class="mt-1 text-sm font-medium text-[#080D21]">{{ $residentCard['membership_label'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-[#0F141E]/50">{{ __('messages.members_main_member') }}</dt>
                            <dd class="mt-1 text-sm font-medium text-[#080D21]">{{ $residentCard['main_member_name'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-[#0F141E]/50">{{ __('messages.users_house') }}</dt>
                            <dd class="mt-1 text-sm font-medium text-[#080D21]">{{ $residentCard['house'] }}</dd>
                        </div>
                    </dl>
                    <p class="mt-4 text-sm text-[#0F141E]/70">{{ $residentCard['linked_label'] }}</p>
                </div>
            </section>
        @endif
    </div>
</x-layouts.admin>
