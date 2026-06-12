@props([
    'user' => null,
    'membershipTypes' => [],
    'committeeRoles' => [],
])

@php
    $isEdit = $user !== null;
    $genderOptions = collect(\App\Enums\Gender::cases())->map(fn ($g) => [
        'value' => $g->value,
        'label' => $g->label(),
    ])->all();
    $houseTypeOptions = collect(\App\Enums\HouseType::cases())->map(fn ($h) => [
        'value' => $h->value,
        'label' => $h->label(),
    ])->all();
    $selectedMembership = old('membership_type', $user?->membership_type ?? \App\Enums\MembershipRole::MainMember->value);
    $selectedCommittee = old('committee_role', $user?->committee_role ?? '');
    $showCommittee = count($committeeRoles) > 0;
@endphp

<div class="space-y-8" x-data="{ membershipType: @json($selectedMembership) }">
    <section class="space-y-4">
        <h3 class="text-sm font-bold uppercase tracking-wide text-[#080D21]">{{ __('messages.users_section_profile') }}</h3>

        <div class="grid gap-4 sm:grid-cols-3">
            <x-common.input
                name="first_name"
                :label="__('messages.users_first_name')"
                :value="old('first_name', $user?->first_name)"
                required
            />
            <x-common.input
                name="middle_name"
                :label="__('messages.users_middle_name')"
                :value="old('middle_name', $user?->middle_name)"
            />
            <x-common.input
                name="last_name"
                :label="__('messages.users_last_name')"
                :value="old('last_name', $user?->last_name)"
                required
            />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-common.input
                name="caste"
                :label="__('messages.users_caste')"
                :value="old('caste', $user?->caste)"
            />
            <x-common.select
                name="gender"
                :label="__('messages.users_gender')"
                :options="$genderOptions"
                :value="old('gender', $user?->gender?->value)"
                required
            >
                <option value="">{{ __('messages.users_select_option') }}</option>
            </x-common.select>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-common.select
                name="house_type"
                :label="__('messages.users_house_type')"
                :options="$houseTypeOptions"
                :value="old('house_type', $user?->house_type?->value)"
                required
            >
                <option value="">{{ __('messages.users_select_option') }}</option>
            </x-common.select>
            <x-common.input
                name="house_number"
                type="text"
                inputmode="numeric"
                pattern="[0-9]*"
                :label="__('messages.users_house_number')"
                :placeholder="__('messages.users_house_number_placeholder')"
                :value="old('house_number', $user?->house_number)"
                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                required
            />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-common.input
                name="mobile_number"
                :label="__('messages.users_mobile')"
                :value="old('mobile_number', $user?->mobile_number)"
                required
            />
            <x-common.input
                name="alternate_number"
                :label="__('messages.users_alternate_mobile')"
                :value="old('alternate_number', $user?->alternate_number)"
            />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-common.file-input
                name="id_proof"
                :label="__('messages.users_id_proof')"
                accept=".pdf,.jpg,.jpeg,.png"
                :hint="__('messages.users_id_proof_hint')"
                :current-url="$user?->idProofUrl()"
            />
            <x-common.file-input
                name="profile_image"
                :label="__('messages.users_profile_image')"
                accept=".jpg,.jpeg,.png,.webp"
                :hint="__('messages.users_profile_image_hint')"
                :current-url="$user?->profileImageUrl()"
                :current-label="__('messages.users_profile_image')"
            />
        </div>
    </section>

    <section class="space-y-4 border-t border-[#E6EBF4] pt-6">
        <h3 class="text-sm font-bold uppercase tracking-wide text-[#080D21]">{{ __('messages.users_section_account') }}</h3>

        @unless ($isEdit)
            {{-- Absorb browser autofill so saved login credentials are not applied to new-user fields --}}
            <input type="text" name="prevent_autofill" tabindex="-1" autocomplete="username" class="pointer-events-none absolute h-0 w-0 opacity-0" aria-hidden="true">
            <input type="password" name="prevent_autofill_password" tabindex="-1" autocomplete="current-password" class="pointer-events-none absolute h-0 w-0 opacity-0" aria-hidden="true">
        @endunless

        <div class="grid gap-4 sm:grid-cols-2">
            <x-common.input
                name="email"
                type="email"
                :label="__('messages.users_email')"
                :value="old('email', $user?->email)"
                autocomplete="off"
                required
            />
            <x-common.input
                name="username"
                :label="__('messages.users_username')"
                :value="old('username', $user?->username)"
                autocomplete="off"
                required
            />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-common.select
                    name="membership_type"
                    :label="__('messages.users_membership_type')"
                    :options="$membershipTypes"
                    :value="$selectedMembership"
                    x-model="membershipType"
                    required
                >
                    <option value="">{{ __('messages.users_select_option') }}</option>
                </x-common.select>
            </div>

            @if ($showCommittee)
                <div x-show="membershipType !== 'rental_member'" x-cloak>
                    <x-common.select
                        name="committee_role"
                        :label="__('messages.users_committee_role')"
                        :options="$committeeRoles"
                        :value="$selectedCommittee"
                        x-bind:disabled="membershipType === 'rental_member'"
                    >
                    </x-common.select>
                    <p class="mt-1 text-xs text-[#0F141E]/50">{{ __('messages.users_committee_hint') }}</p>
                </div>
            @elseif ($isEdit && filled($user?->committee_role))
                <div>
                    <p class="text-sm font-medium text-[#080D21]">{{ __('messages.users_committee_role') }}</p>
                    <p class="mt-1 text-sm text-[#0F141E]">{{ $user->roleLabel() }}</p>
                    <p class="mt-1 text-xs text-[#0F141E]/50">{{ __('messages.users_committee_readonly') }}</p>
                </div>
            @endif
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-common.password
                name="password"
                :label="$isEdit ? __('messages.users_password_optional') : __('messages.users_password')"
                :placeholder="__('messages.users_password_placeholder')"
                autocomplete="new-password"
            />
            <x-common.password
                name="password_confirmation"
                :label="__('messages.users_password_confirm')"
                autocomplete="new-password"
            />
        </div>

        @unless ($isEdit)
            <p class="text-xs text-[#0F141E]/50">{{ __('messages.users_password_auto_hint') }}</p>
        @endunless
    </section>
</div>
