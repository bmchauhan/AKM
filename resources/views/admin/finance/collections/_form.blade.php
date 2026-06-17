@props([
    'collectionTypes' => [],
    'mainMembers' => [],
    'paymentModes' => [],
    'maintenanceChargeLookupUrl' => '',
    'defaultCollectionType' => 'clubhouse_booking',
    'notesFieldId' => 'finance-collection-notes',
    'editingCollection' => null,
])

@php
    $selectedType = old(
        'collection_type',
        $editingCollection?->collection_type?->value ?? $defaultCollectionType,
    );
    $selectedMainMemberId = (int) old(
        'main_member_id',
        $editingCollection?->main_member_id ?? '',
    );
    $amountValue = old('amount', $editingCollection?->amount);
    $maintenanceBaseValue = old('maintenance_base_amount', $editingCollection?->maintenance_base_amount);
    $maintenanceChargeSettingId = old(
        'maintenance_charge_setting_id',
        $editingCollection?->maintenance_charge_setting_id,
    );
    $receivedOnValue = old(
        'received_on',
        $editingCollection?->received_on?->toDateString() ?? now()->toDateString(),
    );
    $paymentModeValue = old(
        'payment_mode',
        $editingCollection?->payment_mode?->value ?? '',
    );
    $referenceValue = old('reference', $editingCollection?->reference);
    $notesValue = old('notes', $editingCollection?->notes);
@endphp

<div
    x-data="{
        collectionType: @js($selectedType),
        mainMembers: @js($mainMembers),
        selectedMainMemberId: @js($selectedMainMemberId),
        receivedOn: @js($receivedOnValue),
        maintenanceBase: @js($maintenanceBaseValue !== null && $maintenanceBaseValue !== '' ? (string) $maintenanceBaseValue : ''),
        maintenanceChargeSettingId: @js($maintenanceChargeSettingId ? (string) $maintenanceChargeSettingId : ''),
        amount: @js($amountValue !== null && $amountValue !== '' ? (string) $amountValue : ''),
        lookupUrl: @js($maintenanceChargeLookupUrl),
        chargeHint: '',
        loadingCharge: false,
        previousReceivedOn: @js($receivedOnValue),
        get isMaintenance() {
            return this.collectionType === 'maintenance';
        },
        houseLabel() {
            const match = this.mainMembers.find((item) => Number(item.value) === Number(this.selectedMainMemberId));
            if (! match || ! match.house_type || ! match.house_number) {
                return '—';
            }
            return `${match.house_type} ${match.house_number}`;
        },
        resetMaintenanceFields() {
            this.maintenanceBase = '';
            this.maintenanceChargeSettingId = '';
            this.amount = '';
            this.chargeHint = '';
            this.previousReceivedOn = this.receivedOn;
        },
        async fetchMaintenanceCharge() {
            if (! this.isMaintenance || ! this.receivedOn) {
                return;
            }
            if (this.receivedOn === this.previousReceivedOn && this.maintenanceBase) {
                return;
            }
            this.previousReceivedOn = this.receivedOn;
            this.loadingCharge = true;
            this.chargeHint = '';
            try {
                const url = new URL(this.lookupUrl, window.location.origin);
                url.searchParams.set('received_on', this.receivedOn);
                const response = await fetch(url.toString(), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (! response.ok) {
                    this.maintenanceBase = '';
                    this.maintenanceChargeSettingId = '';
                    this.amount = '';
                    this.chargeHint = @js(__('messages.finance_maintenance_charge_not_found'));
                    return;
                }
                const data = await response.json();
                this.maintenanceBase = String(data.monthly_amount);
                this.maintenanceChargeSettingId = String(data.maintenance_charge_setting_id);
                this.amount = String(data.monthly_amount);
                let hint = @js(__('messages.finance_maintenance_charge_effective')).replace(':date', data.effective_from);
                if (data.end_date) {
                    hint += ' · ' + @js(__('messages.finance_maintenance_charge_until')).replace(':date', data.end_date);
                }
                this.chargeHint = hint;
            } catch (error) {
                this.chargeHint = @js(__('messages.finance_maintenance_charge_not_found'));
            } finally {
                this.loadingCharge = false;
            }
        },
        onCollectionTypeChange() {
            if (! this.isMaintenance) {
                this.resetMaintenanceFields();
            } else {
                this.fetchMaintenanceCharge();
            }
        },
    }"
    x-init="$watch('collectionType', () => onCollectionTypeChange())"
    class="space-y-5"
>
    <x-common.select
        name="collection_type"
        :label="__('messages.finance_collection_type')"
        :options="$collectionTypes"
        :value="$selectedType"
        x-model="collectionType"
        required
    >
        <option value="">{{ __('messages.users_select_option') }}</option>
    </x-common.select>

    <div x-show="isMaintenance" x-cloak class="space-y-5 rounded-xl border border-[#E6C280]/40 bg-[#E6EBF4]/40 p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-[#080D21]">{{ __('messages.finance_maintenance_collection_section') }}</p>

        <x-common.select
            name="main_member_id"
            :label="__('messages.finance_main_member')"
            :options="collect($mainMembers)->map(fn ($m) => ['value' => $m['value'], 'label' => $m['label']])->all()"
            :value="$selectedMainMemberId ?: ''"
            x-model="selectedMainMemberId"
            x-bind:required="isMaintenance"
            x-bind:disabled="! isMaintenance"
        >
            <option value="">{{ __('messages.finance_select_main_member') }}</option>
        </x-common.select>

        <div class="rounded-lg border border-[#E6EBF4] bg-white px-4 py-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#0F141E]/50">{{ __('messages.users_house') }}</p>
            <p class="mt-1 text-sm font-medium text-[#080D21]" x-text="houseLabel()"></p>
        </div>

        <input type="hidden" name="maintenance_charge_setting_id" x-bind:value="maintenanceChargeSettingId">

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-[#080D21]">
                    {{ __('messages.finance_maintenance_charge_amount') }} <span class="text-[#AB1E23]">*</span>
                </label>
                <input
                    type="number"
                    name="maintenance_base_amount"
                    x-model="maintenanceBase"
                    x-bind:required="isMaintenance"
                    readonly
                    step="0.01"
                    min="0.01"
                    class="w-full rounded-lg border border-[#E6EBF4] bg-[#ECEAE1]/60 px-4 py-2.5 text-sm text-[#0F141E] shadow-sm"
                />
                <p x-show="chargeHint" x-text="chargeHint" class="mt-1 text-xs text-[#0F141E]/60"></p>
                <p x-show="loadingCharge" class="mt-1 text-xs text-[#0F141E]/60">{{ __('messages.finance_maintenance_charge_loading') }}</p>
                @error('maintenance_base_amount')
                    <p class="mt-1.5 text-sm font-medium text-[#AB1E23]">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-[#080D21]">
                    {{ __('messages.finance_amount_received') }} <span class="text-[#AB1E23]">*</span>
                </label>
                <input
                    type="number"
                    name="amount"
                    x-model="amount"
                    x-bind:required="isMaintenance"
                    x-bind:disabled="! isMaintenance"
                    step="0.01"
                    min="0.01"
                    class="w-full rounded-lg border border-[#E6EBF4] bg-white px-4 py-2.5 text-sm font-semibold text-[#AB1E23] shadow-sm transition focus:border-[#AB1E23] focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20"
                    :placeholder="@js(__('messages.finance_collection_partial_placeholder'))"
                />
                <p class="mt-1 text-xs text-[#0F141E]/60">{{ __('messages.finance_collection_partial_hint') }}</p>
                @error('amount')
                    <p class="mt-1.5 text-sm font-medium text-[#AB1E23]">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div x-show="! isMaintenance" x-cloak>
            <label class="mb-1.5 block text-sm font-semibold text-[#080D21]">
                {{ __('messages.finance_amount') }} <span class="text-[#AB1E23]">*</span>
            </label>
            <input
                type="number"
                name="amount"
                value="{{ $amountValue }}"
                x-bind:disabled="isMaintenance"
                step="0.01"
                min="0.01"
                required
                class="w-full rounded-lg border border-[#E6EBF4] bg-white px-4 py-2.5 text-sm text-[#0F141E] shadow-sm transition focus:border-[#AB1E23] focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20"
            />
        </div>

        <x-common.input
            type="date"
            name="received_on"
            :label="__('messages.finance_received_on')"
            :value="$receivedOnValue"
            x-model="receivedOn"
            @change="fetchMaintenanceCharge()"
            max="{{ now()->toDateString() }}"
            required
        />
    </div>

    <x-common.select
        name="payment_mode"
        :label="__('messages.finance_payment_mode')"
        :options="$paymentModes"
        :value="$paymentModeValue"
    >
        <option value="">{{ __('messages.finance_payment_mode_optional') }}</option>
    </x-common.select>

    <x-common.input
        name="reference"
        :label="__('messages.finance_reference')"
        :value="$referenceValue"
        :placeholder="__('messages.finance_collection_reference_placeholder')"
    />

    <div>
        <label for="{{ $notesFieldId }}" class="mb-1.5 block text-sm font-semibold text-[#080D21]">
            {{ __('messages.finance_notes') }}
        </label>
        <textarea
            name="notes"
            id="{{ $notesFieldId }}"
            rows="3"
            class="w-full rounded-lg border border-[#E6EBF4] bg-white px-4 py-2.5 text-sm text-[#0F141E] shadow-sm transition placeholder:text-[#0F141E]/40 focus:border-[#AB1E23] focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20"
            placeholder="{{ __('messages.finance_collection_notes_placeholder') }}"
        >{{ $notesValue }}</textarea>
        @error('notes')
            <p class="mt-1.5 text-sm font-medium text-[#AB1E23]">{{ $message }}</p>
        @enderror
    </div>
</div>
