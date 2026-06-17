@props([
    'expenseTags' => [],
    'workersGrouped' => [],
    'workerSalaryLookupUrl' => '',
    'notesFieldId' => 'finance-expense-notes',
    'editingExpense' => null,
])

@php
    $selectedTag = old('expense_tag', $editingExpense?->expense_tag?->value ?? '');
    $amountValue = old('amount', $editingExpense?->amount);
    $paidOnValue = old('paid_on', $editingExpense?->paid_on?->toDateString() ?? now()->toDateString());
    $payeeNameValue = old('payee_name', $editingExpense?->payee_name);
    $referenceValue = old('reference', $editingExpense?->reference);
    $notesValue = old('notes', $editingExpense?->notes);
    $workerIdValue = old('worker_id', $editingExpense?->worker_id);
    $salaryBaseValue = old('salary_base_amount', $editingExpense?->salary_base_amount);
    $salaryAdjustmentValue = old('salary_adjustment', $editingExpense?->salary_adjustment ?? 0);
    $salaryAdjustmentNoteValue = old('salary_adjustment_note', $editingExpense?->salary_adjustment_note);
    $workerTagMap = [
        'security_payment' => 'security_guard',
        'society_sweeper_payment' => 'sweeper',
        'garbage_collector_payment' => 'garbage_collector',
        'gardener_payment' => 'gardener',
    ];
@endphp

<div
    x-data="{
        expenseTag: @js($selectedTag),
        paidOn: @js($paidOnValue),
        workerId: @js($workerIdValue ? (string) $workerIdValue : ''),
        salaryBase: @js($salaryBaseValue !== null && $salaryBaseValue !== '' ? (string) $salaryBaseValue : ''),
        salaryAdjustment: @js((string) ($salaryAdjustmentValue ?? 0)),
        salaryAdjustmentNote: @js($salaryAdjustmentNoteValue ?? ''),
        workersByType: @js($workersGrouped),
        workerTagMap: @js($workerTagMap),
        lookupUrl: @js($workerSalaryLookupUrl),
        salaryHint: '',
        loadingSalary: false,
        previousWorkerId: @js($workerIdValue ? (string) $workerIdValue : ''),
        previousPaidOn: @js($paidOnValue),
        get requiresWorker() {
            return Object.prototype.hasOwnProperty.call(this.workerTagMap, this.expenseTag);
        },
        get workerOptions() {
            const type = this.workerTagMap[this.expenseTag];
            return type ? (this.workersByType[type] || []) : [];
        },
        get computedAmount() {
            if (! this.requiresWorker) {
                return '';
            }
            const base = parseFloat(this.salaryBase) || 0;
            const adjustment = parseFloat(this.salaryAdjustment) || 0;
            const total = base + adjustment;
            return total > 0 ? total.toFixed(2) : '';
        },
        resetWorkerFields() {
            this.workerId = '';
            this.salaryBase = '';
            this.salaryAdjustment = '0';
            this.salaryAdjustmentNote = '';
            this.salaryHint = '';
            this.previousWorkerId = '';
            this.previousPaidOn = this.paidOn;
        },
        async fetchSalary() {
            if (! this.requiresWorker || ! this.workerId || ! this.paidOn) {
                return;
            }
            this.loadingSalary = true;
            this.salaryHint = '';
            try {
                const url = new URL(this.lookupUrl, window.location.origin);
                url.searchParams.set('worker_id', this.workerId);
                url.searchParams.set('paid_on', this.paidOn);
                const response = await fetch(url.toString(), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (! response.ok) {
                    this.salaryBase = '';
                    this.salaryHint = @js(__('messages.workers_salary_not_found'));
                    return;
                }
                const data = await response.json();
                this.salaryBase = String(data.monthly_salary);
                this.salaryHint = @js(__('messages.finance_expense_salary_effective')).replace(':date', data.effective_from);
            } catch (error) {
                this.salaryHint = @js(__('messages.workers_salary_not_found'));
            } finally {
                this.loadingSalary = false;
            }
        },
        onExpenseTagChange() {
            this.resetWorkerFields();
        },
        onWorkerOrDateChange() {
            if (! this.requiresWorker || ! this.workerId || ! this.paidOn) {
                return;
            }
            if (this.workerId === this.previousWorkerId && this.paidOn === this.previousPaidOn) {
                return;
            }
            this.previousWorkerId = this.workerId;
            this.previousPaidOn = this.paidOn;
            this.fetchSalary();
        },
    }"
    x-init="$watch('expenseTag', () => onExpenseTagChange())"
    class="space-y-5"
>
    <x-common.select
        name="expense_tag"
        :label="__('messages.finance_expense_tag')"
        :options="$expenseTags"
        :value="$selectedTag"
        x-model="expenseTag"
        required
    >
        <option value="">{{ __('messages.users_select_option') }}</option>
    </x-common.select>

    <div x-show="requiresWorker" x-cloak class="space-y-5 rounded-xl border border-[#E6C280]/40 bg-[#E6EBF4]/40 p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-[#080D21]">{{ __('messages.finance_expense_worker_section') }}</p>

        <div>
            <label class="mb-1.5 block text-sm font-semibold text-[#080D21]">
                {{ __('messages.workers_worker') }} <span class="text-[#AB1E23]">*</span>
            </label>
            <select
                name="worker_id"
                x-model="workerId"
                @change="onWorkerOrDateChange()"
                x-bind:required="requiresWorker"
                class="w-full rounded-lg border border-[#E6EBF4] bg-white px-4 py-2.5 text-sm text-[#0F141E] shadow-sm transition focus:border-[#AB1E23] focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20"
            >
                <option value="">{{ __('messages.workers_select_worker') }}</option>
                <template x-for="option in workerOptions" :key="option.value">
                    <option :value="String(option.value)" x-text="option.label" :selected="workerId === String(option.value)"></option>
                </template>
            </select>
            @error('worker_id')
                <p class="mt-1.5 text-sm font-medium text-[#AB1E23]">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-[#080D21]">
                    {{ __('messages.workers_salary_base') }} <span class="text-[#AB1E23]">*</span>
                </label>
                <input
                    type="number"
                    name="salary_base_amount"
                    x-model="salaryBase"
                    x-bind:required="requiresWorker"
                    step="0.01"
                    min="0.01"
                    readonly
                    class="w-full rounded-lg border border-[#E6EBF4] bg-[#ECEAE1]/60 px-4 py-2.5 text-sm text-[#0F141E] shadow-sm"
                />
                <p x-show="salaryHint" x-text="salaryHint" class="mt-1 text-xs text-[#0F141E]/60"></p>
                <p x-show="loadingSalary" class="mt-1 text-xs text-[#0F141E]/60">{{ __('messages.finance_expense_salary_loading') }}</p>
                @error('salary_base_amount')
                    <p class="mt-1.5 text-sm font-medium text-[#AB1E23]">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-[#080D21]">
                    {{ __('messages.workers_salary_adjustment') }}
                </label>
                <input
                    type="number"
                    name="salary_adjustment"
                    x-model="salaryAdjustment"
                    step="0.01"
                    class="w-full rounded-lg border border-[#E6EBF4] bg-white px-4 py-2.5 text-sm text-[#0F141E] shadow-sm transition placeholder:text-[#0F141E]/40 focus:border-[#AB1E23] focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20"
                    :placeholder="@js(__('messages.workers_salary_adjustment_placeholder'))"
                />
                @error('salary_adjustment')
                    <p class="mt-1.5 text-sm font-medium text-[#AB1E23]">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-semibold text-[#080D21]">
                {{ __('messages.workers_salary_adjustment_note') }}
            </label>
            <input
                type="text"
                name="salary_adjustment_note"
                x-model="salaryAdjustmentNote"
                class="w-full rounded-lg border border-[#E6EBF4] bg-white px-4 py-2.5 text-sm text-[#0F141E] shadow-sm transition placeholder:text-[#0F141E]/40 focus:border-[#AB1E23] focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20"
                :placeholder="@js(__('messages.workers_salary_adjustment_note_placeholder'))"
            />
            @error('salary_adjustment_note')
                <p class="mt-1.5 text-sm font-medium text-[#AB1E23]">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-semibold text-[#080D21]">
                {{ __('messages.finance_amount') }} <span class="text-[#AB1E23]">*</span>
            </label>
            <input
                type="number"
                name="amount"
                x-bind:value="computedAmount"
                x-bind:disabled="! requiresWorker"
                readonly
                step="0.01"
                min="0.01"
                class="w-full rounded-lg border border-[#E6EBF4] bg-[#ECEAE1]/60 px-4 py-2.5 text-sm font-semibold text-[#AB1E23] shadow-sm"
            />
            <p class="mt-1 text-xs text-[#0F141E]/60">{{ __('messages.finance_expense_amount_formula') }}</p>
            @error('amount')
                <p class="mt-1.5 text-sm font-medium text-[#AB1E23]">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div x-show="! requiresWorker" x-cloak>
            <label class="mb-1.5 block text-sm font-semibold text-[#080D21]">
                {{ __('messages.finance_amount') }} <span class="text-[#AB1E23]">*</span>
            </label>
            <input
                type="number"
                name="amount"
                value="{{ $amountValue }}"
                x-bind:disabled="requiresWorker"
                step="0.01"
                min="0.01"
                required
                class="w-full rounded-lg border border-[#E6EBF4] bg-white px-4 py-2.5 text-sm text-[#0F141E] shadow-sm transition focus:border-[#AB1E23] focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20"
            />
            @error('amount')
                <p class="mt-1.5 text-sm font-medium text-[#AB1E23]">{{ $message }}</p>
            @enderror
        </div>

        <x-common.input
            type="date"
            name="paid_on"
            :label="__('messages.finance_paid_on')"
            :value="$paidOnValue"
            x-model="paidOn"
            @change="onWorkerOrDateChange()"
            max="{{ now()->toDateString() }}"
            required
        />
    </div>

    <div x-show="! requiresWorker" x-cloak>
        <x-common.input
            name="payee_name"
            :label="__('messages.finance_payee_name')"
            :value="$payeeNameValue"
            :placeholder="__('messages.finance_payee_name_placeholder')"
        />
    </div>

    <x-common.input
        name="reference"
        :label="__('messages.finance_reference')"
        :value="$referenceValue"
        :placeholder="__('messages.finance_expense_reference_placeholder')"
    />

    <div>
        <label for="{{ $notesFieldId }}" class="mb-1.5 block text-sm font-semibold text-[#080D21]">
            {{ __('messages.finance_notes') }}
            <span x-show="expenseTag === 'others'" class="text-[#AB1E23]">*</span>
        </label>
        <textarea
            name="notes"
            id="{{ $notesFieldId }}"
            rows="3"
            x-bind:required="expenseTag === 'others'"
            class="w-full rounded-lg border border-[#E6EBF4] bg-white px-4 py-2.5 text-sm text-[#0F141E] shadow-sm transition placeholder:text-[#0F141E]/40 focus:border-[#AB1E23] focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20"
            placeholder="{{ __('messages.finance_expense_notes_placeholder') }}"
        >{{ $notesValue }}</textarea>
        @error('notes')
            <p class="mt-1.5 text-sm font-medium text-[#AB1E23]">{{ $message }}</p>
        @enderror
    </div>
</div>
