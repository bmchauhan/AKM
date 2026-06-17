@props([
    'editingCharge' => null,
    'statuses' => [],
    'openOnLoad' => false,
])

@if ($editingCharge)
    <div
        x-data="{
            open: @js((bool) $openOnLoad),
            title: @js(__('messages.finance_maintenance_charge_edit')),
        }"
    >
        <x-common.modal maxWidth="max-w-lg">
            <form method="POST" action="{{ route('admin.finance.maintenance-charges.update') }}" class="space-y-5">
                @csrf
                @method('PUT')
                <input type="hidden" name="_finance_form" value="maintenance-charge-edit">

                <div class="rounded-xl border border-[#E6EBF4] bg-[#E6EBF4]/40 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#0F141E]/50">{{ __('messages.finance_maintenance_monthly_amount') }}</p>
                    <p class="mt-1 text-lg font-bold text-[#AB1E23]">{{ number_format((float) $editingCharge->monthly_amount, 2) }}</p>
                    <p class="mt-1 text-xs text-[#0F141E]/60">
                        {{ __('messages.finance_maintenance_effective_from') }}: {{ $editingCharge->effective_from->format('d M Y') }}
                    </p>
                </div>

                <p class="text-sm text-[#0F141E]/70">{{ __('messages.finance_maintenance_charge_edit_hint') }}</p>

                <x-common.select
                    name="status"
                    :label="__('messages.finance_maintenance_status')"
                    :options="$statuses"
                    :value="old('status', $editingCharge->status?->value ?? $editingCharge->status)"
                    required
                />

                <x-common.input
                    type="date"
                    name="end_date"
                    :label="__('messages.finance_maintenance_end_date')"
                    :value="old('end_date', $editingCharge->end_date?->toDateString())"
                />

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-[#080D21]">{{ __('messages.finance_notes') }}</label>
                    <textarea
                        name="notes"
                        rows="2"
                        class="w-full rounded-lg border border-[#E6EBF4] bg-white px-4 py-2.5 text-sm text-[#0F141E] shadow-sm transition focus:border-[#AB1E23] focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20"
                    >{{ old('notes', $editingCharge->notes) }}</textarea>
                </div>

                <div class="flex flex-wrap justify-end gap-3 border-t border-[#E6EBF4] pt-4">
                    <x-common.button type="button" variant="secondary" :href="route('admin.finance.maintenance-charges.cancel-edit')">
                        {{ __('messages.finance_cancel') }}
                    </x-common.button>
                    <x-common.button type="submit">
                        {{ __('messages.finance_maintenance_charge_update') }}
                    </x-common.button>
                </div>
            </form>
        </x-common.modal>
    </div>
@endif
