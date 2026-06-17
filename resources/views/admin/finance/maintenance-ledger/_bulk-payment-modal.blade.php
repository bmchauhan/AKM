@props([
    'member' => null,
    'allocationEntries' => [],
    'totalOutstandingRaw' => 0,
    'paymentModes' => [],
    'houseReturn' => [],
])

@can('finance.update')
    @if ($member && count($allocationEntries) > 0)
        <div
            x-data="{
                open: false,
                title: @js(__('messages.finance_ledger_bulk_title')),
                amount: '',
                entries: @js($allocationEntries),
                totalOutstanding: @js((float) $totalOutstandingRaw),
                currency: '₹',
                formatMoney(value) {
                    const n = Number(value) || 0;
                    return this.currency + n.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },
                round(value) {
                    return Math.round((Number(value) || 0) * 100) / 100;
                },
                get preview() {
                    const payment = this.round(this.amount);
                    let remaining = payment;
                    const cleared = [];
                    let partial = null;
                    const stillDue = [];
                    let totalOutstanding = 0;
                    let allocated = 0;

                    for (const entry of this.entries) {
                        const outstanding = this.round(entry.outstanding);
                        if (outstanding <= 0) continue;
                        totalOutstanding = this.round(totalOutstanding + outstanding);

                        if (remaining <= 0) {
                            stillDue.push({ ...entry, applied: 0, new_paid: entry.amount_paid });
                            continue;
                        }

                        if (remaining >= outstanding - 0.01) {
                            const applied = outstanding;
                            const newPaid = this.round(entry.amount_paid + applied);
                            remaining = this.round(remaining - applied);
                            allocated = this.round(allocated + applied);
                            cleared.push({ ...entry, applied, new_paid: newPaid, result_status: 'paid' });
                        } else {
                            const applied = remaining;
                            const newPaid = this.round(entry.amount_paid + applied);
                            allocated = this.round(allocated + applied);
                            partial = { ...entry, applied, new_paid: newPaid, result_status: 'partial' };
                            remaining = 0;
                        }
                    }

                    return {
                        payment,
                        totalOutstanding,
                        allocated,
                        excess: this.round(Math.max(0, remaining)),
                        cleared,
                        partial,
                        stillDue,
                        hasAmount: payment > 0,
                    };
                },
                statusLabel(status) {
                    return {
                        paid: @js(__('messages.finance_ledger_status_paid')),
                        partial: @js(__('messages.finance_ledger_status_partial')),
                        due: @js(__('messages.finance_ledger_status_due')),
                        pending: @js(__('messages.finance_ledger_status_pending')),
                    }[status] ?? status;
                },
                badgeClass(status) {
                    return {
                        paid: 'bg-[#E6C280]/50 text-[#080D21]',
                        partial: 'bg-[#E6C280]/35 text-[#080D21]',
                        due: 'bg-[#E5989B]/25 text-[#AB1E23]',
                        pending: 'bg-[#E6EBF4] text-[#080D21]',
                    }[status] ?? 'bg-[#E6EBF4] text-[#080D21]';
                },
            }"
        >
            <x-common.button type="button" x-on:click="open = true">
                {{ __('messages.finance_ledger_bulk_open') }}
            </x-common.button>

            <x-common.modal maxWidth="max-w-3xl">
                <div class="space-y-5">
                    <div class="rounded-xl border border-[#E6EBF4] bg-[#E6EBF4]/40 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-[#0F141E]/50">{{ __('messages.finance_main_member') }}</p>
                        <p class="mt-1 text-sm font-bold text-[#080D21]">{{ $member->houseLabel() }} — {{ $member->fullName() }}</p>
                        <p class="mt-2 text-xs text-[#0F141E]/60">
                            {{ __('messages.finance_house_ledger_outstanding') }}:
                            <span class="font-semibold text-[#AB1E23]">{{ number_format((float) $totalOutstandingRaw, 2) }}</span>
                            <span class="text-[#0F141E]/50">({{ count($allocationEntries) }} {{ __('messages.finance_ledger_bulk_months_unpaid') }})</span>
                        </p>
                    </div>

                    <form method="POST" action="{{ route('admin.finance.maintenance-ledger.apply-bulk-payment') }}" class="space-y-5">
                        @csrf
                        <input type="hidden" name="main_member_id" value="{{ $member->id }}">
                        <input type="hidden" name="house" value="{{ $houseReturn['house'] ?? $member->houseLabel() }}">
                        <input type="hidden" name="status_filter" value="{{ $houseReturn['status_filter'] ?? 'outstanding' }}">

                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-common.input
                                type="number"
                                name="payment_amount"
                                :label="__('messages.finance_ledger_bulk_amount')"
                                x-model="amount"
                                step="0.01"
                                min="0.01"
                                :placeholder="__('messages.finance_ledger_bulk_amount_placeholder')"
                                required
                            />
                            <x-common.input
                                type="date"
                                name="paid_on"
                                :label="__('messages.finance_paid_on')"
                                :value="now()->toDateString()"
                                max="{{ now()->toDateString() }}"
                            />
                        </div>

                        <p class="text-xs text-[#0F141E]/60">{{ __('messages.finance_ledger_bulk_hint') }}</p>

                        <template x-if="preview.hasAmount">
                            <div class="space-y-4">
                                <div class="grid gap-3 sm:grid-cols-3">
                                    <div class="rounded-lg border border-[#E6EBF4] bg-white p-3">
                                        <p class="text-xs font-semibold uppercase text-[#0F141E]/50">{{ __('messages.finance_ledger_bulk_allocated') }}</p>
                                        <p class="mt-1 text-lg font-bold text-[#AB1E23]" x-text="formatMoney(preview.allocated)"></p>
                                    </div>
                                    <div class="rounded-lg border border-[#E6EBF4] bg-white p-3" x-show="preview.excess > 0">
                                        <p class="text-xs font-semibold uppercase text-[#0F141E]/50">{{ __('messages.finance_ledger_bulk_excess') }}</p>
                                        <p class="mt-1 text-lg font-bold text-[#080D21]" x-text="formatMoney(preview.excess)"></p>
                                    </div>
                                    <div class="rounded-lg border border-[#E6EBF4] bg-white p-3">
                                        <p class="text-xs font-semibold uppercase text-[#0F141E]/50">{{ __('messages.finance_ledger_bulk_still_due') }}</p>
                                        <p class="mt-1 text-lg font-bold text-[#080D21]" x-text="formatMoney(Math.max(0, preview.totalOutstanding - preview.allocated))"></p>
                                    </div>
                                </div>

                                <template x-if="preview.cleared.length > 0">
                                    <div>
                                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-[#080D21]">{{ __('messages.finance_ledger_bulk_cleared') }}</p>
                                        <div class="overflow-hidden rounded-lg border border-[#E6C280]/40">
                                            <table class="min-w-full text-sm">
                                                <thead class="bg-[#E6C280]/20 text-left text-xs uppercase text-[#080D21]">
                                                    <tr>
                                                        <th class="px-3 py-2">{{ __('messages.finance_billing_month') }}</th>
                                                        <th class="px-3 py-2 text-right">{{ __('messages.finance_maintenance_charge_amount') }}</th>
                                                        <th class="px-3 py-2 text-right">{{ __('messages.finance_ledger_bulk_applied') }}</th>
                                                        <th class="px-3 py-2 text-right">{{ __('messages.finance_amount_received') }}</th>
                                                        <th class="px-3 py-2">{{ __('messages.finance_maintenance_status') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <template x-for="row in preview.cleared" :key="row.id">
                                                        <tr class="border-t border-[#E6EBF4]">
                                                            <td class="px-3 py-2 font-medium text-[#080D21]" x-text="row.billing_month_label"></td>
                                                            <td class="px-3 py-2 text-right" x-text="formatMoney(row.charge_amount)"></td>
                                                            <td class="px-3 py-2 text-right font-semibold text-[#AB1E23]" x-text="formatMoney(row.applied)"></td>
                                                            <td class="px-3 py-2 text-right" x-text="formatMoney(row.new_paid)"></td>
                                                            <td class="px-3 py-2">
                                                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium" :class="badgeClass('paid')" x-text="statusLabel('paid')"></span>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="preview.partial">
                                    <div>
                                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-[#080D21]">{{ __('messages.finance_ledger_bulk_partial_section') }}</p>
                                        <div class="overflow-hidden rounded-lg border border-[#E6C280]/40">
                                            <table class="min-w-full text-sm">
                                                <thead class="bg-[#E6C280]/15 text-left text-xs uppercase text-[#080D21]">
                                                    <tr>
                                                        <th class="px-3 py-2">{{ __('messages.finance_billing_month') }}</th>
                                                        <th class="px-3 py-2 text-right">{{ __('messages.finance_maintenance_charge_amount') }}</th>
                                                        <th class="px-3 py-2 text-right">{{ __('messages.finance_ledger_bulk_applied') }}</th>
                                                        <th class="px-3 py-2 text-right">{{ __('messages.finance_amount_received') }}</th>
                                                        <th class="px-3 py-2 text-right">{{ __('messages.finance_ledger_bulk_balance') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="border-t border-[#E6EBF4]">
                                                        <td class="px-3 py-2 font-medium" x-text="preview.partial.billing_month_label"></td>
                                                        <td class="px-3 py-2 text-right" x-text="formatMoney(preview.partial.charge_amount)"></td>
                                                        <td class="px-3 py-2 text-right font-semibold text-[#AB1E23]" x-text="formatMoney(preview.partial.applied)"></td>
                                                        <td class="px-3 py-2 text-right" x-text="formatMoney(preview.partial.new_paid)"></td>
                                                        <td class="px-3 py-2 text-right text-[#AB1E23]" x-text="formatMoney(preview.partial.charge_amount - preview.partial.new_paid)"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="preview.stillDue.length > 0">
                                    <div>
                                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-[#AB1E23]">{{ __('messages.finance_ledger_bulk_still_due_list') }}</p>
                                        <div class="overflow-hidden rounded-lg border border-[#E5989B]/30">
                                            <table class="min-w-full text-sm">
                                                <thead class="bg-[#E5989B]/10 text-left text-xs uppercase text-[#AB1E23]">
                                                    <tr>
                                                        <th class="px-3 py-2">{{ __('messages.finance_billing_month') }}</th>
                                                        <th class="px-3 py-2 text-right">{{ __('messages.finance_maintenance_charge_amount') }}</th>
                                                        <th class="px-3 py-2 text-right">{{ __('messages.finance_amount_received') }}</th>
                                                        <th class="px-3 py-2 text-right">{{ __('messages.finance_ledger_bulk_balance') }}</th>
                                                        <th class="px-3 py-2">{{ __('messages.finance_maintenance_status') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <template x-for="row in preview.stillDue" :key="'due-' + row.id">
                                                        <tr class="border-t border-[#E6EBF4]">
                                                            <td class="px-3 py-2 font-medium" x-text="row.billing_month_label"></td>
                                                            <td class="px-3 py-2 text-right" x-text="formatMoney(row.charge_amount)"></td>
                                                            <td class="px-3 py-2 text-right" x-text="formatMoney(row.amount_paid)"></td>
                                                            <td class="px-3 py-2 text-right font-semibold text-[#AB1E23]" x-text="formatMoney(row.outstanding)"></td>
                                                            <td class="px-3 py-2">
                                                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium" :class="badgeClass(row.status)" x-text="statusLabel(row.status)"></span>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-common.select name="payment_mode" :label="__('messages.finance_payment_mode')" :options="$paymentModes">
                                <option value="">{{ __('messages.finance_payment_mode_optional') }}</option>
                            </x-common.select>
                            <x-common.input name="reference" :label="__('messages.finance_reference')" />
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-[#080D21]">{{ __('messages.finance_notes') }}</label>
                            <textarea
                                name="notes"
                                rows="2"
                                class="w-full rounded-lg border border-[#E6EBF4] bg-white px-4 py-2.5 text-sm text-[#0F141E] shadow-sm transition focus:border-[#AB1E23] focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20"
                            ></textarea>
                        </div>

                        <div class="flex flex-wrap justify-end gap-3 border-t border-[#E6EBF4] pt-4">
                            <x-common.button type="button" variant="secondary" x-on:click="open = false">
                                {{ __('messages.finance_cancel') }}
                            </x-common.button>
                            <x-common.button type="submit">
                                {{ __('messages.finance_ledger_bulk_apply') }}
                            </x-common.button>
                        </div>
                    </form>
                </div>
            </x-common.modal>
        </div>
    @endif
@endcan
