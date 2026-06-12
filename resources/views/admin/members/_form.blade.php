@props([
    'member' => null,
    'mainMembers' => [],
    'membershipTypes' => [],
    'defaultMainMemberId' => null,
    'canPickMainMember' => true,
])

@php
    $isEdit = $member !== null;
    $genderOptions = collect(\App\Enums\Gender::cases())->map(fn ($g) => [
        'value' => $g->value,
        'label' => $g->label(),
    ])->all();
    $houseTypeOptions = collect(\App\Enums\HouseType::cases())->map(fn ($h) => [
        'value' => $h->value,
        'label' => $h->label(),
    ])->all();
    $mainMemberOptions = collect($mainMembers)->map(fn ($m) => [
        'value' => $m['value'],
        'label' => $m['label'],
    ])->all();
    $selectedMainMemberId = old('linked_main_member_id', $member?->linked_main_member_id ?? $defaultMainMemberId);
    $selectedMembershipType = old('membership_type', $member?->role ?? \App\Enums\MembershipRole::FamilyMember->value);
@endphp

<div
    x-data="{
        mainMembers: @js($mainMembers),
        membershipType: @js($selectedMembershipType),
        selectedMainMemberId: @js((int) $selectedMainMemberId),
        applyHouseFromMainMember() {
            const match = this.mainMembers.find((item) => Number(item.value) === Number(this.selectedMainMemberId));
            if (! match) return;
            const houseType = document.getElementById('house_type');
            const houseNumber = document.getElementById('house_number');
            if (houseType && match.house_type) houseType.value = match.house_type;
            if (houseNumber && match.house_number) houseNumber.value = match.house_number;
        }
    }"
    class="space-y-8"
    x-init="if (selectedMainMemberId) applyHouseFromMainMember()"
>
    <section class="space-y-4">
        <h3 class="text-sm font-bold uppercase tracking-wide text-[#080D21]">{{ __('messages.members_section_household') }}</h3>

        <x-common.select
            name="membership_type"
            :label="__('messages.members_type')"
            :options="$membershipTypes"
            :value="$selectedMembershipType"
            x-model="membershipType"
            required
        >
            <option value="">{{ __('messages.users_select_option') }}</option>
        </x-common.select>

        @if ($canPickMainMember)
            <x-common.searchable-select
                name="linked_main_member_id"
                :label="__('messages.members_main_member')"
                :options="$mainMemberOptions"
                :value="$selectedMainMemberId"
                :placeholder="__('messages.members_select_main_member')"
                required
                x-on:searchable-select-changed="selectedMainMemberId = Number($event.detail.value) || ''; applyHouseFromMainMember()"
            />
        @else
            <input type="hidden" name="linked_main_member_id" value="{{ $defaultMainMemberId }}">
            <div class="rounded-xl border border-[#E6EBF4] bg-[#E6EBF4]/40 px-4 py-3 text-sm text-[#0F141E]/80">
                {{ __('messages.members_main_member_self', ['name' => auth()->user()->fullName()]) }}
            </div>
        @endif

        <p class="text-xs text-[#0F141E]/60" x-show="membershipType === 'family_member'">{{ __('messages.members_family_role_hint') }}</p>
        <p class="text-xs text-[#0F141E]/60" x-show="membershipType === 'rental_member'" x-cloak>{{ __('messages.members_rental_role_hint') }}</p>
    </section>

    <section class="space-y-4 border-t border-[#E6EBF4] pt-6">
        <h3 class="text-sm font-bold uppercase tracking-wide text-[#080D21]">{{ __('messages.users_section_profile') }}</h3>

        <div class="grid gap-4 sm:grid-cols-3">
            <x-common.input name="first_name" :label="__('messages.users_first_name')" :value="old('first_name', $member?->first_name)" required />
            <x-common.input name="middle_name" :label="__('messages.users_middle_name')" :value="old('middle_name', $member?->middle_name)" />
            <x-common.input name="last_name" :label="__('messages.users_last_name')" :value="old('last_name', $member?->last_name)" required />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-common.input name="caste" :label="__('messages.users_caste')" :value="old('caste', $member?->caste)" />
            <x-common.select name="gender" :label="__('messages.users_gender')" :options="$genderOptions" :value="old('gender', $member?->gender?->value)" required>
                <option value="">{{ __('messages.users_select_option') }}</option>
            </x-common.select>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-common.select name="house_type" :label="__('messages.users_house_type')" :options="$houseTypeOptions" :value="old('house_type', $member?->house_type?->value)" required>
                <option value="">{{ __('messages.users_select_option') }}</option>
            </x-common.select>
            <x-common.input
                name="house_number"
                type="text"
                inputmode="numeric"
                pattern="[0-9]*"
                :label="__('messages.users_house_number')"
                :placeholder="__('messages.users_house_number_placeholder')"
                :value="old('house_number', $member?->house_number)"
                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                required
            />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-common.input name="mobile_number" :label="__('messages.users_mobile')" :value="old('mobile_number', $member?->mobile_number)" required />
            <x-common.input name="alternate_number" :label="__('messages.users_alternate_mobile')" :value="old('alternate_number', $member?->alternate_number)" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-common.file-input name="id_proof" :label="__('messages.users_id_proof')" accept=".pdf,.jpg,.jpeg,.png" :hint="__('messages.users_id_proof_hint')" :current-url="$member?->idProofUrl()" />
            <x-common.file-input name="profile_image" :label="__('messages.users_profile_image')" accept=".jpg,.jpeg,.png,.webp" :hint="__('messages.users_profile_image_hint')" :current-url="$member?->profileImageUrl()" :current-label="__('messages.users_profile_image')" />
        </div>
    </section>

    <section class="space-y-4 border-t border-[#E6EBF4] pt-6">
        <h3 class="text-sm font-bold uppercase tracking-wide text-[#080D21]">{{ __('messages.users_section_account') }}</h3>

        @unless ($isEdit)
            <input type="text" name="prevent_autofill" tabindex="-1" autocomplete="username" class="pointer-events-none absolute h-0 w-0 opacity-0" aria-hidden="true">
            <input type="password" name="prevent_autofill_password" tabindex="-1" autocomplete="current-password" class="pointer-events-none absolute h-0 w-0 opacity-0" aria-hidden="true">
        @endunless

        <div class="grid gap-4 sm:grid-cols-2">
            <x-common.input name="email" type="email" :label="__('messages.users_email')" :value="old('email', $member?->email)" autocomplete="off" required />
            <x-common.input name="username" :label="__('messages.users_username')" :value="old('username', $member?->username)" autocomplete="off" required />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-common.password
                name="password"
                :label="$isEdit ? __('messages.users_password_optional') : __('messages.users_password')"
                :placeholder="__('messages.users_password_placeholder')"
                autocomplete="new-password"
            />
            <x-common.password name="password_confirmation" :label="__('messages.users_password_confirm')" autocomplete="new-password" />
        </div>

        @unless ($isEdit)
            <p class="text-xs text-[#0F141E]/50">{{ __('messages.users_password_auto_hint') }}</p>
        @endunless
    </section>
</div>
