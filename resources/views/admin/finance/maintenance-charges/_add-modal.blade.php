@props([
    'openOnLoad' => false,
])

<div
    x-data="{
        open: @js((bool) $openOnLoad),
        title: @js(__('messages.finance_maintenance_charge_add')),
    }"
>
    @can('finance.update')
        <x-common.button type="button" @click="open = true">
            {{ __('messages.finance_maintenance_charge_add') }}
        </x-common.button>
    @endcan

    <x-common.modal maxWidth="max-w-lg">
        <form method="POST" action="{{ route('admin.finance.maintenance-charges.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="_finance_form" value="maintenance-charge">

            <p class="text-sm text-[#0F141E]/70">{{ __('messages.finance_maintenance_charge_add_hint') }}</p>

            <x-common.input
                type="number"
                name="monthly_amount"
                :label="__('messages.finance_maintenance_monthly_amount')"
                :value="old('monthly_amount')"
                step="0.01"
                min="0.01"
                required
            />

            <div class="grid gap-5 sm:grid-cols-2">
                <x-common.input
                    type="date"
                    name="effective_from"
                    :label="__('messages.finance_maintenance_effective_from')"
                    :value="old('effective_from', now()->toDateString())"
                    required
                />
                <x-common.input
                    type="date"
                    name="end_date"
                    :label="__('messages.finance_maintenance_end_date')"
                    :value="old('end_date')"
                />
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-[#080D21]">{{ __('messages.finance_notes') }}</label>
                <textarea
                    name="notes"
                    rows="2"
                    class="w-full rounded-lg border border-[#E6EBF4] bg-white px-4 py-2.5 text-sm text-[#0F141E] shadow-sm transition placeholder:text-[#0F141E]/40 focus:border-[#AB1E23] focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20"
                    placeholder="{{ __('messages.finance_maintenance_charge_notes_placeholder') }}"
                >{{ old('notes') }}</textarea>
            </div>

            <div class="flex flex-wrap justify-end gap-3 border-t border-[#E6EBF4] pt-4">
                <x-common.button type="button" variant="secondary" @click="open = false">
                    {{ __('messages.finance_cancel') }}
                </x-common.button>
                <x-common.button type="submit">
                    {{ __('messages.finance_maintenance_charge_save') }}
                </x-common.button>
            </div>
        </form>
    </x-common.modal>
</div>
