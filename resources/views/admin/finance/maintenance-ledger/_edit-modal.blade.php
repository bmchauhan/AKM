@props([
    'editingEntry' => null,
    'paymentModes' => [],
    'openOnLoad' => false,
    'cancelEditUrl' => null,
    'houseReturn' => [],
])

@if ($editingEntry)
    @php
        $member = $editingEntry->mainMember;
        $status = $editingEntry->status;
        $resolvedCancelUrl = $cancelEditUrl ?? route('admin.finance.maintenance-ledger.cancel-edit', [
            'month' => $editingEntry->billing_month->format('Y-m'),
        ]);
    @endphp
    <div
        x-data="{
            open: @js((bool) $openOnLoad),
            title: @js(__('messages.finance_ledger_edit_entry')),
            amountPaid: @js((string) old('amount_paid', $editingEntry->amount_paid)),
            chargeAmount: @js((string) $editingEntry->charge_amount),
            cancelUrl: @js($resolvedCancelUrl),
            init() {
                this.$watch('open', (value, oldValue) => {
                    if (oldValue === true && value === false) {
                        window.location.href = this.cancelUrl;
                    }
                });
            },
        }"
    >
        <x-common.modal maxWidth="max-w-lg">
            <form method="POST" action="{{ route('admin.finance.maintenance-ledger.update') }}" class="space-y-5">
                @csrf
                @method('PUT')
                <input type="hidden" name="_finance_form" value="ledger-edit">
                @foreach ($houseReturn as $name => $value)
                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                @endforeach

                <div class="rounded-xl border border-[#E6EBF4] bg-[#E6EBF4]/40 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#0F141E]/50">{{ __('messages.users_house') }}</p>
                    <p class="mt-1 text-sm font-bold text-[#080D21]">{{ $member?->houseLabel() }} — {{ $member?->fullName() }}</p>
                    <p class="mt-2 text-xs text-[#0F141E]/60">
                        {{ __('messages.finance_maintenance_charge_amount') }}:
                        <span class="font-semibold text-[#AB1E23]">{{ number_format((float) $editingEntry->charge_amount, 2) }}</span>
                    </p>
                </div>

                <x-common.input
                    type="number"
                    name="amount_paid"
                    :label="__('messages.finance_amount_received')"
                    :value="old('amount_paid', $editingEntry->amount_paid)"
                    x-model="amountPaid"
                    step="0.01"
                    min="0"
                    :max="$editingEntry->charge_amount"
                    required
                />

                <p class="text-xs text-[#0F141E]/60">{{ __('messages.finance_ledger_partial_hint') }}</p>

                <x-common.input
                    type="date"
                    name="paid_on"
                    :label="__('messages.finance_paid_on')"
                    :value="old('paid_on', $editingEntry->paid_on?->toDateString() ?? now()->toDateString())"
                    max="{{ now()->toDateString() }}"
                />

                <x-common.select
                    name="payment_mode"
                    :label="__('messages.finance_payment_mode')"
                    :options="$paymentModes"
                    :value="old('payment_mode', $editingEntry->payment_mode?->value ?? '')"
                >
                    <option value="">{{ __('messages.finance_payment_mode_optional') }}</option>
                </x-common.select>

                <x-common.input
                    name="reference"
                    :label="__('messages.finance_reference')"
                    :value="old('reference', $editingEntry->reference)"
                />

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-[#080D21]">{{ __('messages.finance_notes') }}</label>
                    <textarea
                        name="notes"
                        rows="2"
                        class="w-full rounded-lg border border-[#E6EBF4] bg-white px-4 py-2.5 text-sm text-[#0F141E] shadow-sm transition focus:border-[#AB1E23] focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20"
                    >{{ old('notes', $editingEntry->notes) }}</textarea>
                </div>

                <div class="flex flex-wrap justify-end gap-3 border-t border-[#E6EBF4] pt-4">
                    <x-common.button type="button" variant="secondary" :href="$resolvedCancelUrl">
                        {{ __('messages.finance_cancel') }}
                    </x-common.button>
                    <x-common.button type="submit">
                        {{ __('messages.finance_ledger_save') }}
                    </x-common.button>
                </div>
            </form>
        </x-common.modal>
    </div>
@endif
