<x-layouts.admin :pageTitle="__('messages.finance_maintenance_charges')">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#AB1E23]">{{ __('messages.finance') }}</p>
                <h2 class="mt-1 text-xl font-bold text-[#080D21]">{{ __('messages.finance_maintenance_charges') }}</h2>
                <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.finance_maintenance_charges_subtitle') }}</p>
            </div>

            @include('admin.finance.maintenance-charges._add-modal', [
                'openOnLoad' => $openAddChargeModal ?? false,
            ])
        </div>

        @if ($currentCharge)
            <div class="rounded-xl border border-[#E6C280]/50 bg-[#E6EBF4]/60 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-[#080D21]/60">{{ __('messages.finance_maintenance_current_charge') }}</p>
                <p class="mt-2 text-3xl font-bold text-[#080D21]">{{ $currentCharge['formatted_amount'] }}</p>
                <p class="mt-2 text-sm text-[#0F141E]/70">
                    {{ __('messages.finance_maintenance_charge_effective', ['date' => $currentCharge['effective_from']]) }}
                    @if ($currentCharge['end_date'])
                        · {{ __('messages.finance_maintenance_charge_until', ['date' => $currentCharge['end_date']]) }}
                    @endif
                </p>
            </div>
        @else
            <div class="rounded-xl border border-[#E5989B]/40 bg-[#E5989B]/10 p-5 shadow-sm">
                <p class="text-sm font-medium text-[#080D21]">{{ __('messages.finance_maintenance_charge_not_configured') }}</p>
                <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.finance_maintenance_charge_not_configured_hint') }}</p>
            </div>
        @endif

        <div class="overflow-hidden rounded-xl border border-[#E6EBF4] bg-white shadow-sm">
            <div class="hidden border-b border-[#E6EBF4] bg-[#E6EBF4]/50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-[#080D21] md:grid md:grid-cols-12 md:gap-2">
                <div class="md:col-span-2">{{ __('messages.finance_maintenance_monthly_amount') }}</div>
                <div class="md:col-span-2">{{ __('messages.finance_maintenance_effective_from') }}</div>
                <div class="md:col-span-2">{{ __('messages.finance_maintenance_end_date') }}</div>
                <div class="md:col-span-2">{{ __('messages.finance_maintenance_status') }}</div>
                <div class="md:col-span-3">{{ __('messages.finance_recorded_by') }}</div>
                <div class="md:col-span-1 text-right">{{ __('messages.users_actions') }}</div>
            </div>

            @forelse ($charges as $charge)
                <div class="border-b border-[#E6EBF4] px-4 py-3 last:border-b-0 md:grid md:grid-cols-12 md:items-center md:gap-2">
                    <div class="mb-1 text-sm font-semibold text-[#AB1E23] md:col-span-2 md:mb-0">{{ $charge['monthly_amount'] }}</div>
                    <div class="mb-1 text-sm text-[#0F141E] md:col-span-2 md:mb-0">{{ $charge['effective_from'] }}</div>
                    <div class="mb-1 text-sm text-[#0F141E]/70 md:col-span-2 md:mb-0">{{ $charge['end_date'] }}</div>
                    <div class="mb-2 md:col-span-2 md:mb-0">
                        @if ($charge['is_active'])
                            <span class="inline-flex rounded-full bg-[#E6C280]/30 px-2 py-0.5 text-xs font-medium text-[#080D21]">{{ $charge['status_label'] }}</span>
                        @else
                            <span class="inline-flex rounded-full bg-[#E5989B]/20 px-2 py-0.5 text-xs font-medium text-[#080D21]">{{ $charge['status_label'] }}</span>
                        @endif
                    </div>
                    <div class="mb-2 text-sm text-[#0F141E]/70 md:col-span-3 md:mb-0">
                        {{ $charge['set_by'] }}
                        @if ($charge['notes'])
                            <p class="mt-0.5 text-xs text-[#0F141E]/50">{{ $charge['notes'] }}</p>
                        @endif
                    </div>
                    <div class="flex items-center justify-end md:col-span-1">
                        @can('finance_maintenance_charges.update')
                            <form method="POST" action="{{ route('admin.finance.maintenance-charges.open-edit') }}" class="inline-flex">
                                @csrf
                                <input type="hidden" name="maintenance_charge_id" value="{{ $charge['id'] }}">
                                <x-common.icon-action type="submit" :title="__('messages.finance_edit')">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </x-common.icon-action>
                            </form>
                        @endcan
                    </div>
                </div>
            @empty
                <div class="px-4 py-10 text-center text-sm text-[#0F141E]/60">
                    {{ __('messages.finance_maintenance_charges_empty') }}
                </div>
            @endforelse
        </div>

        @if ($charges->hasPages())
            <div class="rounded-xl border border-[#E6EBF4] bg-white px-4 py-3">
                {{ $charges->links() }}
            </div>
        @endif
    </div>

    @include('admin.finance.maintenance-charges._edit-modal', [
        'editingCharge' => $editingCharge ?? null,
        'statuses' => $statuses,
        'openOnLoad' => $openEditChargeModal ?? false,
    ])
</x-layouts.admin>
