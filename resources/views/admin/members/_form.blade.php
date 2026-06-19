@props([
    'member' => null,
    'mainMembers' => [],
    'membershipTypes' => [],
    'defaultMainMemberId' => null,
    'canPickMainMember' => true,
    'canChooseHouseholdScope' => false,
    'defaultHouseholdScope' => 'self',
])

@php
    $isEdit = $member !== null;
    $actor = auth()->user();
    $actorMainMemberId = $actor?->id;
    $genderOptions = collect(\App\Enums\Gender::cases())->map(fn ($g) => [
        'value' => $g->value,
        'label' => $g->label(),
    ])->all();
    $mainMemberOptions = collect($mainMembers)->map(fn ($m) => [
        'value' => $m['value'],
        'label' => $m['label'],
    ])->all();
    $defaultHouseholdScope = old(
        'household_scope',
        $isEdit
            ? ((int) $member?->linked_main_member_id !== (int) $actorMainMemberId ? 'others' : 'self')
            : $defaultHouseholdScope,
    );
    $selectedMainMemberId = old(
        'linked_main_member_id',
        $defaultHouseholdScope === 'others'
            ? ($member?->linked_main_member_id ?? $defaultMainMemberId)
            : $actorMainMemberId,
    );
    $selectedMembershipType = old('membership_type', $member?->membership_type ?? $member?->role ?? \App\Enums\MembershipRole::FamilyMember->value);
    $initialHouseType = old('house_type', $member?->house_type?->value ?? $actor?->house_type?->value ?? '');
    $initialHouseNumber = old('house_number', $member?->house_number ?? $actor?->house_number ?? '');
    $initialHouseDisplay = filled($initialHouseType) && filled($initialHouseNumber)
        ? trim($initialHouseType.' '.$initialHouseNumber)
        : ($member?->houseLabel() ?? $actor?->houseLabel() ?? '');
@endphp

<div
    x-data="{
        mainMembers: @js($mainMembers),
        membershipType: @js($selectedMembershipType),
        householdScope: @js($defaultHouseholdScope),
        actorMainMemberId: @js((int) $actorMainMemberId),
        selectedMainMemberId: @js((int) $selectedMainMemberId),
        houseTypeValue: @js($initialHouseType),
        houseNumberValue: @js($initialHouseNumber),
        houseDisplay: @js($initialHouseDisplay),
        applyHouseFromMainMember() {
            const targetId = this.householdScope === 'self'
                ? this.actorMainMemberId
                : Number(this.selectedMainMemberId);
            const match = this.mainMembers.find((item) => Number(item.value) === targetId);
            if (! match) return;
            if (match.house_type) {
                this.houseTypeValue = match.house_type;
            }
            if (match.house_number) {
                this.houseNumberValue = match.house_number;
            }
            if (match.house_type && match.house_number) {
                this.houseDisplay = `${match.house_type} ${match.house_number}`;
            }
        }
    }"
    class="space-y-8"
    x-init="applyHouseFromMainMember(); membershipType = document.getElementById('membership_type')?.value || membershipType"
    x-effect="applyHouseFromMainMember()"
>
    <section class="space-y-4">
        <h3 class="text-sm font-bold uppercase tracking-wide text-[#080D21]">{{ __('messages.members_section_household') }}</h3>

        <x-common.select
            name="membership_type"
            :label="__('messages.members_type')"
            :options="$membershipTypes"
            :value="$selectedMembershipType"
            x-on:change="membershipType = $event.target.value"
            required
        >
            <option value="">{{ __('messages.users_select_option') }}</option>
        </x-common.select>

        @if ($canChooseHouseholdScope)
            <input
                type="hidden"
                name="linked_main_member_id"
                :value="householdScope === 'self' ? actorMainMemberId : (selectedMainMemberId || '')"
            >

            <div class="space-y-3">
                <p class="text-sm font-semibold text-[#080D21]">{{ __('messages.members_household_scope_label') }}</p>
                <div class="flex flex-wrap gap-3">
                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-[#E6EBF4] bg-white px-4 py-2.5 text-sm font-medium text-[#0F141E] shadow-sm transition has-[:checked]:border-[#AB1E23] has-[:checked]:bg-[#E6EBF4]">
                        <input
                            type="radio"
                            name="household_scope"
                            value="self"
                            class="h-4 w-4 border-[#E6EBF4] text-[#AB1E23] focus:ring-[#AB1E23]/20"
                            x-model="householdScope"
                        >
                        {{ __('messages.members_household_scope_self') }}
                    </label>
                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-[#E6EBF4] bg-white px-4 py-2.5 text-sm font-medium text-[#0F141E] shadow-sm transition has-[:checked]:border-[#AB1E23] has-[:checked]:bg-[#E6EBF4]">
                        <input
                            type="radio"
                            name="household_scope"
                            value="others"
                            class="h-4 w-4 border-[#E6EBF4] text-[#AB1E23] focus:ring-[#AB1E23]/20"
                            x-model="householdScope"
                        >
                        {{ __('messages.members_household_scope_others') }}
                    </label>
                </div>
            </div>

            <div x-show="householdScope === 'self'" x-cloak>
                <div class="rounded-xl border border-[#E6EBF4] bg-[#E6EBF4]/40 px-4 py-3 text-sm text-[#0F141E]/80">
                    {{ __('messages.members_main_member_self', ['name' => $actor->fullName()]) }}
                    @if ($actor->houseLabel())
                        <span class="mt-1 block text-xs text-[#0F141E]/60">{{ $actor->houseLabel() }}</span>
                    @endif
                </div>
            </div>

            <div x-show="householdScope === 'others'" x-cloak>
                <x-common.searchable-select
                    name="main_member_picker"
                    :label="__('messages.members_main_member')"
                    :options="$mainMemberOptions"
                    :value="$selectedMainMemberId"
                    :placeholder="__('messages.members_select_main_member')"
                    x-on:searchable-select-changed="selectedMainMemberId = Number($event.detail.value) || ''; applyHouseFromMainMember()"
                />
            </div>
        @elseif ($canPickMainMember)
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
                {{ __('messages.members_main_member_self', ['name' => $actor->fullName()]) }}
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

        <div>
            <input type="hidden" name="house_type" :value="houseTypeValue">
            <input type="hidden" name="house_number" :value="houseNumberValue">
            <p class="text-sm font-medium text-[#080D21]">{{ __('messages.users_house') }}</p>
            <p class="mt-1 text-sm text-[#0F141E]" x-text="houseDisplay || '—'"></p>
            <p class="mt-1 text-xs text-[#0F141E]/50">{{ __('messages.members_house_locked_hint') }}</p>
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

        <div @class(['grid gap-4', 'sm:grid-cols-2' => $isEdit])>
            <div>
                <x-common.input name="email" type="email" :label="__('messages.users_email')" :value="old('email', $member?->email)" autocomplete="off" />
                <p class="mt-1.5 text-xs text-[#0F141E]/50">{{ __('messages.users_email_optional_hint') }}</p>
            </div>
            @if ($isEdit)
                <x-admin.username-display :value="$member->username" />
            @endif
        </div>

        @unless ($isEdit)
            <p class="rounded-lg border border-[#E6EBF4] bg-[#E6EBF4]/50 px-4 py-3 text-sm leading-relaxed text-[#0F141E]/75">
                {{ __('messages.members_password_auto_notice') }}
                {{ ' ' }}
                {{ __('messages.users_username_auto_notice_create') }}
            </p>
        @else
            <div class="grid gap-4 sm:grid-cols-2">
                <x-common.password
                    name="password"
                    :label="__('messages.users_password_optional')"
                    :placeholder="__('messages.users_password_placeholder')"
                    autocomplete="new-password"
                />
                <x-common.password name="password_confirmation" :label="__('messages.users_password_confirm')" autocomplete="new-password" />
            </div>
        @endunless
    </section>
</div>
