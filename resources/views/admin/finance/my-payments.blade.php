<x-layouts.admin :pageTitle="__('messages.finance_my_payments')">
    <div class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-[#AB1E23]">{{ __('messages.finance') }}</p>
            <h2 class="mt-1 text-xl font-bold text-[#080D21]">{{ __('messages.finance_my_payments') }}</h2>
            <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.finance_my_payments_subtitle') }}</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-admin.stat-card
                :label="__('messages.users_house')"
                :value="$house ?? '—'"
            />
            <x-admin.stat-card
                :label="__('messages.finance_my_payments_total')"
                :value="$total_received"
                :hint="__('messages.finance_my_payments_total_hint')"
            />
        </div>

        <div class="overflow-hidden rounded-xl border border-[#E6EBF4] bg-white">
            <div class="hidden border-b border-[#E6EBF4] bg-[#E6EBF4]/50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-[#080D21] md:grid md:grid-cols-12 md:gap-2">
                <div class="md:col-span-2">{{ __('messages.finance_received_on') }}</div>
                <div class="md:col-span-2">{{ __('messages.finance_collection_type') }}</div>
                <div class="md:col-span-2">{{ __('messages.finance_maintenance_status') }}</div>
                <div class="md:col-span-2 text-right">{{ __('messages.finance_amount') }}</div>
                <div class="md:col-span-4">{{ __('messages.finance_reference') }}</div>
            </div>

            @forelse ($payments as $payment)
                <div class="border-b border-[#E6EBF4] px-4 py-3 last:border-b-0 md:grid md:grid-cols-12 md:items-center md:gap-2">
                    <div class="mb-1 text-sm text-[#0F141E] md:col-span-2 md:mb-0">{{ $payment['received_on'] }}</div>
                    <div class="mb-1 md:col-span-2 md:mb-0">
                        <span class="inline-flex rounded-full bg-[#E6EBF4] px-2.5 py-0.5 text-xs font-medium text-[#080D21]">
                            {{ $payment['type_label'] }}
                        </span>
                    </div>
                    <div class="mb-1 md:col-span-2 md:mb-0">
                        @if ($payment['status_label'])
                            <span class="inline-flex rounded-full bg-[#ECEAE1] px-2.5 py-0.5 text-xs font-medium text-[#080D21]">
                                {{ $payment['status_label'] }}
                            </span>
                        @else
                            <span class="text-sm text-[#0F141E]/40">—</span>
                        @endif
                    </div>
                    <div class="mb-1 text-right text-sm font-semibold text-[#080D21] md:col-span-2 md:mb-0">{{ $payment['amount'] }}</div>
                    <div class="text-sm text-[#0F141E]/70 md:col-span-4">
                        {{ $payment['reference'] ?? '—' }}
                        @if ($payment['notes'])
                            <p class="mt-0.5 text-xs text-[#0F141E]/50">{{ $payment['notes'] }}</p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-4 py-10 text-center text-sm text-[#0F141E]/60">
                    {{ __('messages.finance_my_payments_empty') }}
                </div>
            @endforelse
        </div>

        @if ($payments->hasPages())
            <div class="rounded-xl border border-[#E6EBF4] bg-white px-4 py-3">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin>
