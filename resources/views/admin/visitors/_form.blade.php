@php
    $partySize = (int) old('party_size', 1);
    $maleCount = (int) old('male_count', 1);
    $femaleCount = (int) old('female_count', 0);
    $childrenCount = (int) old('children_count', 0);
@endphp

<div
    x-data="{
        houseUnitId: @js(old('house_unit_id', '')),
        hostUserId: @js(old('host_user_id', '')),
        hosts: [],
        hostsLoading: false,
        partySize: @js($partySize),
        maleCount: @js($maleCount),
        femaleCount: @js($femaleCount),
        childrenCount: @js($childrenCount),
        hostsUrl: @js(route('admin.visitors.house-hosts')),
        get selectedHost() {
            return this.hosts.find(h => String(h.id) === String(this.hostUserId)) ?? null;
        },
        get isRentalVisit() {
            return this.selectedHost?.is_rental === true;
        },
        get partyMismatch() {
            return this.partySize !== (this.maleCount + this.femaleCount + this.childrenCount);
        },
        async loadHosts() {
            this.hostUserId = '';
            this.hosts = [];
            if (!this.houseUnitId) return;
            this.hostsLoading = true;
            try {
                const res = await fetch(this.hostsUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                    },
                    body: JSON.stringify({ house_unit_id: this.houseUnitId }),
                });
                const data = await res.json();
                this.hosts = data.hosts ?? [];
            } catch (e) {
                this.hosts = [];
            } finally {
                this.hostsLoading = false;
            }
        }
    }"
    x-init="
        if (houseUnitId) loadHosts().then(() => { hostUserId = @js(old('host_user_id', '')); });
    "
    class="space-y-6"
>
    <div class="grid gap-4 sm:grid-cols-2">
        <x-common.searchable-select
            name="house_unit_id"
            :label="__('messages.visitors_house')"
            :options="collect($houses)->map(fn ($house) => ['value' => $house['id'], 'label' => $house['label']])->all()"
            :value="old('house_unit_id')"
            :placeholder="__('messages.visitors_select_house')"
            required
            x-on:searchable-select-changed="houseUnitId = $event.detail.value; loadHosts()"
        />

        <div>
            <label for="host_user_id" class="mb-1.5 block text-sm font-semibold text-[#080D21]">
                {{ __('messages.visitors_host') }} <span class="text-[#AB1E23]">*</span>
            </label>
            <select
                id="host_user_id"
                required
                x-model="hostUserId"
                :disabled="!houseUnitId || hostsLoading || hosts.length === 0"
                class="w-full rounded-lg border border-[#E6EBF4] bg-white px-3 py-2.5 text-sm text-[#0F141E] shadow-sm focus:border-[#AB1E23] focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20 disabled:cursor-not-allowed disabled:opacity-60"
            >
                <option value="">{{ __('messages.visitors_select_host') }}</option>
                <template x-for="host in hosts" :key="host.id">
                    <option :value="host.id" x-text="host.name + ' (' + host.membership_label + ')'"></option>
                </template>
            </select>
            <input type="hidden" name="host_user_id" x-bind:value="hostUserId" />
            <p class="mt-1 text-xs text-[#0F141E]/60" x-show="hostsLoading" x-cloak>{{ __('messages.visitors_loading_hosts') }}</p>
            @error('host_user_id')
                <p class="mt-1.5 text-sm font-medium text-[#AB1E23]">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div
        x-show="isRentalVisit"
        x-cloak
        class="rounded-lg border border-[#E6C280] bg-[#E6C280]/20 px-4 py-3 text-sm text-[#080D21]"
    >
        {{ __('messages.visitors_rental_notice') }}
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <x-common.input
            name="visitor_name"
            :label="__('messages.visitors_name')"
            :value="old('visitor_name')"
            required
        />
        <x-common.input
            name="visitor_contact"
            :label="__('messages.visitors_contact')"
            :value="old('visitor_contact')"
            :placeholder="__('messages.workers_mobile_placeholder')"
            required
        />
    </div>

    <div class="rounded-lg border border-[#E6EBF4] bg-[#E6EBF4]/40 p-4">
        <p class="mb-3 text-sm font-semibold text-[#080D21]">{{ __('messages.visitors_party_heading') }}</p>
        <p class="mb-3 text-sm text-[#0F141E]/70" x-show="partySize === 1" x-cloak>{{ __('messages.visitors_party_single_hint') }}</p>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="party_size" class="mb-1.5 block text-sm font-semibold text-[#080D21]">
                    {{ __('messages.visitors_party_total') }} <span class="text-[#AB1E23]">*</span>
                </label>
                <input
                    type="number"
                    name="party_size"
                    id="party_size"
                    min="1"
                    max="20"
                    required
                    x-model.number="partySize"
                    class="w-full rounded-lg border border-[#E6EBF4] bg-white px-4 py-2.5 text-sm text-[#0F141E] shadow-sm focus:border-[#AB1E23] focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20"
                />
            </div>
            <div>
                <label for="male_count" class="mb-1.5 block text-sm font-semibold text-[#080D21]">{{ __('messages.visitors_party_male') }}</label>
                <input type="number" name="male_count" id="male_count" min="0" max="20" x-model.number="maleCount" class="w-full rounded-lg border border-[#E6EBF4] bg-white px-4 py-2.5 text-sm text-[#0F141E] shadow-sm focus:border-[#AB1E23] focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20" />
            </div>
            <div>
                <label for="female_count" class="mb-1.5 block text-sm font-semibold text-[#080D21]">{{ __('messages.visitors_party_female') }}</label>
                <input type="number" name="female_count" id="female_count" min="0" max="20" x-model.number="femaleCount" class="w-full rounded-lg border border-[#E6EBF4] bg-white px-4 py-2.5 text-sm text-[#0F141E] shadow-sm focus:border-[#AB1E23] focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20" />
            </div>
            <div>
                <label for="children_count" class="mb-1.5 block text-sm font-semibold text-[#080D21]">{{ __('messages.visitors_party_children') }}</label>
                <input type="number" name="children_count" id="children_count" min="0" max="20" x-model.number="childrenCount" class="w-full rounded-lg border border-[#E6EBF4] bg-white px-4 py-2.5 text-sm text-[#0F141E] shadow-sm focus:border-[#AB1E23] focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20" />
            </div>
        </div>
        <p x-show="partyMismatch" x-cloak class="mt-2 text-sm font-medium text-[#AB1E23]">{{ __('messages.visitors_party_size_mismatch') }}</p>
        @error('party_size')
            <p class="mt-1.5 text-sm font-medium text-[#AB1E23]">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <x-common.input
            name="vehicle_number"
            :label="__('messages.visitors_vehicle')"
            :value="old('vehicle_number')"
            :placeholder="__('messages.visitors_vehicle_placeholder')"
        />
        <x-common.input
            name="purpose"
            :label="__('messages.visitors_purpose')"
            :value="old('purpose')"
            :placeholder="__('messages.visitors_purpose_placeholder')"
        />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="id_proof" class="mb-1.5 block text-sm font-semibold text-[#080D21]">
                {{ __('messages.visitors_id_proof') }} <span class="text-[#AB1E23]">*</span>
            </label>
            <input
                type="file"
                name="id_proof"
                id="id_proof"
                accept=".pdf,.jpg,.jpeg,.png"
                required
                class="w-full rounded-lg border border-[#E6EBF4] bg-white px-3 py-2 text-sm text-[#0F141E] file:mr-3 file:rounded file:border-0 file:bg-[#E6EBF4] file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-[#080D21]"
            />
            <p class="mt-1 text-xs text-[#0F141E]/60">{{ __('messages.visitors_id_proof_hint') }}</p>
            @error('id_proof')
                <p class="mt-1.5 text-sm font-medium text-[#AB1E23]">{{ $message }}</p>
            @enderror
        </div>

        <div x-show="isRentalVisit" x-cloak>
            <label for="photo" class="mb-1.5 block text-sm font-semibold text-[#080D21]">
                {{ __('messages.visitors_photo') }} <span class="text-[#AB1E23]">*</span>
            </label>
            <input
                type="file"
                name="photo"
                id="photo"
                accept=".jpg,.jpeg,.png,.webp"
                :required="isRentalVisit"
                capture="environment"
                class="w-full rounded-lg border border-[#E6EBF4] bg-white px-3 py-2 text-sm text-[#0F141E] file:mr-3 file:rounded file:border-0 file:bg-[#E6EBF4] file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-[#080D21]"
            />
            <p class="mt-1 text-xs text-[#0F141E]/60">{{ __('messages.visitors_photo_hint') }}</p>
            @error('photo')
                <p class="mt-1.5 text-sm font-medium text-[#AB1E23]">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="notes" class="mb-1.5 block text-sm font-semibold text-[#080D21]">{{ __('messages.visitors_notes') }}</label>
        <textarea
            name="notes"
            id="notes"
            rows="2"
            class="w-full rounded-lg border border-[#E6EBF4] bg-white px-4 py-2.5 text-sm text-[#0F141E] shadow-sm focus:border-[#AB1E23] focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20"
            placeholder="{{ __('messages.visitors_notes_placeholder') }}"
        >{{ old('notes') }}</textarea>
    </div>
</div>
