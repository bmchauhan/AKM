@props([
    'salaryWorkerId' => null,
    'openOnLoad' => false,
])

<div
    x-data="{
        open: @js((bool) $openOnLoad),
        title: @js(__('messages.workers_salary_revision')),
    }"
>
    <x-common.modal maxWidth="max-w-lg">
        <form method="POST" action="{{ route('admin.workers.salary.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="_worker_form" value="salary">

            <p class="text-sm text-[#0F141E]/70">{{ __('messages.workers_salary_revision_hint') }}</p>

            <input type="hidden" name="worker_id" value="{{ old('worker_id', $salaryWorkerId) }}">

            <x-common.input
                type="number"
                name="monthly_salary"
                :label="__('messages.workers_new_monthly_salary')"
                :value="old('monthly_salary')"
                step="0.01"
                min="0.01"
                required
            />

            <x-common.input
                type="date"
                name="effective_from"
                :label="__('messages.workers_salary_effective_from')"
                :value="old('effective_from', now()->toDateString())"
                required
            />

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-[#080D21]">{{ __('messages.finance_notes') }}</label>
                <textarea
                    name="notes"
                    rows="2"
                    class="w-full rounded-lg border border-[#E6EBF4] bg-white px-4 py-2.5 text-sm text-[#0F141E] shadow-sm transition placeholder:text-[#0F141E]/40 focus:border-[#AB1E23] focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20"
                    placeholder="{{ __('messages.workers_salary_revision_notes_placeholder') }}"
                >{{ old('notes') }}</textarea>
            </div>

            <div class="flex flex-wrap justify-end gap-3 border-t border-[#E6EBF4] pt-4">
                <x-common.button type="button" variant="secondary" @click="open = false">
                    {{ __('messages.finance_cancel') }}
                </x-common.button>
                <x-common.button type="submit">
                    {{ __('messages.workers_salary_save') }}
                </x-common.button>
            </div>
        </form>
    </x-common.modal>
</div>
