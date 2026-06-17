<x-layouts.admin :pageTitle="__('messages.finance_fund_setting')">
    <div class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-[#AB1E23]">
                {{ __('messages.finance') }}
            </p>
            <h2 class="mt-1 text-xl font-bold text-[#080D21]">{{ __('messages.finance_fund_setting') }}</h2>
            <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.finance_fund_setting_subtitle') }}</p>
        </div>

        @if ($is_configured)
            <div class="rounded-xl border border-[#E6C280]/50 bg-[#E6EBF4]/60 p-5 shadow-sm sm:p-6">
                <p class="text-xs font-semibold uppercase tracking-wide text-[#080D21]/60">
                    {{ __('messages.finance_opening_balance_current') }}
                </p>
                <p class="mt-2 text-3xl font-bold text-[#080D21]">{{ $formatted_opening_balance }}</p>
                <p class="mt-2 text-sm text-[#0F141E]/70">
                    {{ __('messages.finance_opening_balance_effective', ['date' => $formatted_effective_date]) }}
                </p>
                @if ($setting?->notes)
                    <p class="mt-3 text-sm text-[#0F141E]/80">{{ $setting->notes }}</p>
                @endif
                @if ($setting?->setBy)
                    <p class="mt-3 text-xs text-[#0F141E]/50">
                        {{ __('messages.finance_fund_setting_set_by', ['name' => $setting->setBy->fullName()]) }}
                    </p>
                @endif
            </div>
        @else
            <div class="rounded-xl border border-[#E5989B]/40 bg-[#E5989B]/10 p-5 shadow-sm">
                <p class="text-sm font-medium text-[#080D21]">{{ __('messages.finance_fund_setting_not_configured') }}</p>
                <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.finance_fund_setting_not_configured_hint') }}</p>
            </div>
        @endif

        @can('super-admin')
            <form
                method="POST"
                action="{{ route('admin.finance.fund-setting.update') }}"
                class="rounded-xl border border-[#E6EBF4] bg-white p-5 shadow-sm sm:p-6"
            >
                @csrf
                @method('PUT')

                <h3 class="text-sm font-bold uppercase tracking-wide text-[#080D21]">
                    {{ $is_configured ? __('messages.finance_fund_setting_update') : __('messages.finance_fund_setting_configure') }}
                </h3>
                <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.finance_fund_setting_sa_only') }}</p>

                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    <x-common.input
                        type="number"
                        name="opening_balance"
                        :label="__('messages.finance_opening_balance')"
                        :value="old('opening_balance', $setting?->opening_balance ?? '0.00')"
                        step="0.01"
                        min="0"
                        required
                    />

                    <x-common.input
                        type="date"
                        name="opening_balance_effective_date"
                        :label="__('messages.finance_opening_balance_date')"
                        :value="old('opening_balance_effective_date', $setting?->opening_balance_effective_date?->toDateString() ?? $defaultEffectiveDate)"
                        required
                    />

                    <div class="sm:col-span-2">
                        <label for="notes" class="mb-1.5 block text-sm font-semibold text-[#080D21]">
                            {{ __('messages.finance_notes') }}
                        </label>
                        <textarea
                            name="notes"
                            id="notes"
                            rows="3"
                            placeholder="{{ __('messages.finance_fund_setting_notes_placeholder') }}"
                            class="w-full rounded-lg border border-[#E6EBF4] bg-white px-4 py-2.5 text-sm text-[#0F141E] shadow-sm transition placeholder:text-[#0F141E]/40 focus:border-[#AB1E23] focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20"
                        >{{ old('notes', $setting?->notes) }}</textarea>
                        @error('notes')
                            <p class="mt-1.5 text-sm font-medium text-[#AB1E23]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <x-common.button type="submit">
                        {{ __('messages.finance_fund_setting_save') }}
                    </x-common.button>
                </div>
            </form>
        @else
            <p class="text-sm text-[#0F141E]/60">{{ __('messages.finance_fund_setting_read_only') }}</p>
        @endcan
    </div>
</x-layouts.admin>
