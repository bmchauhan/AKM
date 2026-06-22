@php
    $isWorkerSalaryView = ($view_mode ?? 'member') === 'worker_salary';
@endphp

<x-layouts.admin :pageTitle="__('messages.finance_my_payments')">
    <div class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-[#AB1E23]">{{ __('messages.finance') }}</p>
            <h2 class="mt-1 text-xl font-bold text-[#080D21]">{{ __('messages.finance_my_payments') }}</h2>
            <p class="mt-1 text-sm text-[#0F141E]/70">
                {{ $isWorkerSalaryView ? __('messages.finance_my_payments_worker_subtitle') : __('messages.finance_my_payments_subtitle') }}
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-admin.stat-card
                :label="$isWorkerSalaryView ? ($profile_label ?? __('messages.workers_name')) : __('messages.users_house')"
                :value="$isWorkerSalaryView ? ($profile_value ?? '—') : ($house ?? '—')"
            />
            <x-admin.stat-card
                :label="__('messages.finance_my_payments_total')"
                :value="$total_received"
                :hint="$isWorkerSalaryView ? __('messages.finance_my_payments_worker_total_hint') : __('messages.finance_my_payments_total_hint')"
            />
        </div>

        <div class="overflow-hidden rounded-xl border border-[#E6EBF4] bg-white">
            <div class="hidden border-b border-[#E6EBF4] bg-[#E6EBF4]/50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-[#080D21] md:grid md:grid-cols-12 md:gap-2">
                <div class="md:col-span-2">
                    {{ $isWorkerSalaryView ? __('messages.finance_paid_on') : __('messages.finance_received_on') }}
                </div>
                <div class="md:col-span-2">{{ __('messages.finance_collection_type') }}</div>
                <div class="md:col-span-2">{{ __('messages.finance_receipt_number') }}</div>
                <div class="md:col-span-2 text-right">{{ __('messages.finance_amount') }}</div>
                <div class="md:col-span-3">{{ __('messages.finance_reference') }}</div>
                <div class="md:col-span-1 text-right">{{ __('messages.finance_receipt_download') }}</div>
            </div>

            @forelse ($payments as $payment)
                <div class="border-b border-[#E6EBF4] px-4 py-3 last:border-b-0 md:grid md:grid-cols-12 md:items-center md:gap-2">
                    <div class="mb-1 text-sm text-[#0F141E] md:col-span-2 md:mb-0">{{ $payment['received_on'] }}</div>
                    <div class="mb-1 md:col-span-2 md:mb-0">
                        <span class="inline-flex rounded-full bg-[#E6EBF4] px-2.5 py-0.5 text-xs font-medium text-[#080D21]">
                            {{ $payment['type_label'] }}
                        </span>
                        @if ($payment['status_label'])
                            <p class="mt-1 text-xs text-[#0F141E]/55">{{ $payment['status_label'] }}</p>
                        @endif
                    </div>
                    <div class="mb-1 text-sm text-[#0F141E]/80 md:col-span-2 md:mb-0">{{ $payment['receipt_number'] }}</div>
                    <div class="mb-1 text-right text-sm font-semibold text-[#080D21] md:col-span-2 md:mb-0">{{ $payment['amount'] }}</div>
                    <div class="mb-2 text-sm text-[#0F141E]/70 md:col-span-3 md:mb-0">
                        {{ $payment['reference'] ?? '—' }}
                        @if ($payment['notes'])
                            <p class="mt-0.5 text-xs text-[#0F141E]/50">{{ $payment['notes'] }}</p>
                        @endif
                    </div>
                    <div class="flex justify-end md:col-span-1">
                        <a
                            href="{{ route('admin.finance.my-payments.receipt.download', $payment['receipt_id']) }}"
                            class="inline-flex items-center gap-1 rounded bg-[#AB1E23] px-2.5 py-1.5 text-xs font-medium text-[#E6EBF4] shadow transition hover:bg-[#E6C280] hover:text-[#080D21]"
                            title="{{ __('messages.finance_receipt_download') }}"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            PDF
                        </a>
                    </div>
                </div>
            @empty
                <div class="px-4 py-10 text-center text-sm text-[#0F141E]/60">
                    {{ $isWorkerSalaryView ? __('messages.finance_my_payments_worker_empty') : __('messages.finance_my_payments_empty') }}
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
