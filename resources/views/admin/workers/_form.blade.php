@props([
    'worker' => null,
    'activeTab' => null,
    'showSalaryFields' => false,
    'showStatusField' => false,
])

@php
    $nameValue = old('name', $worker?->name);
    $mobileValue = old('mobile_number', $worker?->mobile_number);
    $addressValue = old('address', $worker?->address);
    $joinedOnValue = old('joined_on', $worker?->joined_on?->toDateString());
    $notesValue = old('notes', $worker?->notes);
    $isActive = old('is_active', $worker?->is_active ?? true);
    $monthlySalaryValue = old('monthly_salary');
    $salaryEffectiveFromValue = old('salary_effective_from', $joinedOnValue ?? now()->toDateString());
@endphp

<div class="space-y-5">
    @if ($showSalaryFields && $activeTab)
        <input type="hidden" name="worker_type" value="{{ old('worker_type', $activeTab) }}">
    @endif

    <x-common.input
        name="name"
        :label="__('messages.workers_name')"
        :value="$nameValue"
        required
    />

    <div class="grid gap-5 sm:grid-cols-2">
        <x-common.input
            name="mobile_number"
            :label="__('messages.workers_mobile')"
            :value="$mobileValue"
            :placeholder="__('messages.workers_mobile_placeholder')"
        />

        <x-common.input
            type="date"
            name="joined_on"
            :label="__('messages.workers_joined_on')"
            :value="$joinedOnValue"
        />
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-[#080D21]">{{ __('messages.workers_address') }}</label>
        <textarea
            name="address"
            rows="2"
            class="w-full rounded-lg border border-[#E6EBF4] bg-white px-4 py-2.5 text-sm text-[#0F141E] shadow-sm transition placeholder:text-[#0F141E]/40 focus:border-[#AB1E23] focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20"
            placeholder="{{ __('messages.workers_address_placeholder') }}"
        >{{ $addressValue }}</textarea>
        @error('address')
            <p class="mt-1.5 text-sm font-medium text-[#AB1E23]">{{ $message }}</p>
        @enderror
    </div>

    <x-common.file-input
        name="profile_image"
        :label="__('messages.users_profile_image')"
        accept=".jpg,.jpeg,.png,.webp"
        :hint="__('messages.users_profile_image_hint')"
        :current-url="$worker?->profileImageUrl()"
        :current-label="__('messages.users_profile_image')"
    />

    @if ($showSalaryFields)
        <div class="rounded-xl border border-[#E6C280]/40 bg-[#E6EBF4]/40 p-4 space-y-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#080D21]">{{ __('messages.workers_initial_salary') }}</p>
            <div class="grid gap-5 sm:grid-cols-2">
                <x-common.input
                    type="number"
                    name="monthly_salary"
                    :label="__('messages.workers_monthly_salary')"
                    :value="$monthlySalaryValue"
                    step="0.01"
                    min="0.01"
                    required
                />
                <x-common.input
                    type="date"
                    name="salary_effective_from"
                    :label="__('messages.workers_salary_effective_from')"
                    :value="$salaryEffectiveFromValue"
                />
            </div>
        </div>
    @endif

    @if ($showStatusField)
        <label class="flex items-center gap-3 rounded-lg border border-[#E6EBF4] bg-[#E6EBF4]/30 px-4 py-3">
            <input
                type="checkbox"
                name="is_active"
                value="1"
                @checked($isActive)
                class="h-4 w-4 rounded border-[#E6EBF4] text-[#AB1E23] focus:ring-[#AB1E23]/20"
            />
            <span class="text-sm font-medium text-[#080D21]">{{ __('messages.workers_active') }}</span>
        </label>
    @endif

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-[#080D21]">{{ __('messages.finance_notes') }}</label>
        <textarea
            name="notes"
            rows="2"
            class="w-full rounded-lg border border-[#E6EBF4] bg-white px-4 py-2.5 text-sm text-[#0F141E] shadow-sm transition placeholder:text-[#0F141E]/40 focus:border-[#AB1E23] focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20"
            placeholder="{{ __('messages.workers_notes_placeholder') }}"
        >{{ $notesValue }}</textarea>
        @error('notes')
            <p class="mt-1.5 text-sm font-medium text-[#AB1E23]">{{ $message }}</p>
        @enderror
    </div>
</div>
