@php
    $avatarUrl = $user->profileImageUrl()
        ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=E6EBF4&color=080D21&size=128&bold=true';
    $profileEmailRequired = \App\Support\UserEmailRules::requiresEmail(
        $user->membership_type,
        $user->committee_role,
        $user->isSuperAdmin(),
    );
@endphp

<x-layouts.admin :pageTitle="__('messages.profile_my')">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <img
                    src="{{ $avatarUrl }}"
                    alt="{{ $user->fullName() }}"
                    class="h-20 w-20 rounded-full border-2 border-[#E6C280]/50 object-cover"
                />
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#AB1E23]">{{ __('messages.profile_my') }}</p>
                    <h2 class="mt-1 text-xl font-bold text-[#080D21]">{{ $user->fullName() }}</h2>
                    <p class="mt-1 text-sm text-[#0F141E]/70">{{ $user->roleLabelWithShortForm() }}</p>
                </div>
            </div>
            <x-common.button type="button" variant="secondary" :href="route('admin.password.edit')">
                {{ __('messages.profile_reset_password') }}
            </x-common.button>
        </div>

        <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="space-y-6" novalidate>
            @csrf
            @method('PUT')

            <div class="rounded-xl border border-[#E6EBF4] bg-white p-4 sm:p-6">
                <h3 class="text-sm font-bold uppercase tracking-wide text-[#080D21]">{{ __('messages.profile_editable_section') }}</h3>
                <p class="mt-1 text-sm text-[#0F141E]/60">{{ __('messages.profile_editable_hint') }}</p>

                <div class="mt-6 space-y-4">
                    <div class="grid gap-4 sm:grid-cols-3">
                        <x-common.input name="first_name" :label="__('messages.users_first_name')" :value="old('first_name', $user->first_name)" required />
                        <x-common.input name="middle_name" :label="__('messages.users_middle_name')" :value="old('middle_name', $user->middle_name)" />
                        <x-common.input name="last_name" :label="__('messages.users_last_name')" :value="old('last_name', $user->last_name)" required />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-common.input name="email" type="email" :label="__('messages.users_email')" :value="old('email', $user->email)" :required="$profileEmailRequired" />
                            @unless ($profileEmailRequired)
                                <p class="mt-1.5 text-xs text-[#0F141E]/50">{{ __('messages.users_email_optional_hint') }}</p>
                            @endunless
                        </div>
                        <x-common.input name="mobile_number" :label="__('messages.users_mobile')" :value="old('mobile_number', $user->mobile_number)" required />
                    </div>

                    <x-common.input name="alternate_number" :label="__('messages.users_alternate_mobile')" :value="old('alternate_number', $user->alternate_number)" />

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-common.file-input name="id_proof" :label="__('messages.users_id_proof')" accept=".pdf,.jpg,.jpeg,.png" :hint="__('messages.users_id_proof_hint')" :current-url="$user->idProofUrl()" />
                        <x-common.file-input name="profile_image" :label="__('messages.users_profile_image')" accept=".jpg,.jpeg,.png,.webp" :hint="__('messages.users_profile_image_hint')" :current-url="$user->profileImageUrl()" :current-label="__('messages.users_profile_image')" />
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-[#E6EBF4] bg-[#E6EBF4]/40 p-4 sm:p-6">
                <h3 class="text-sm font-bold uppercase tracking-wide text-[#080D21]">{{ __('messages.profile_readonly_section') }}</h3>
                <p class="mt-1 text-sm text-[#0F141E]/60">{{ __('messages.profile_readonly_hint') }}</p>

                <dl class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-lg border border-[#E6EBF4] bg-white px-4 py-3">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-[#0F141E]/60">{{ __('messages.users_username') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-[#080D21]">{{ $user->username ?? '—' }}</dd>
                    </div>
                    <div class="rounded-lg border border-[#E6EBF4] bg-white px-4 py-3">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-[#0F141E]/60">{{ __('messages.users_gender') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-[#080D21]">{{ $user->gender?->label() ?? '—' }}</dd>
                    </div>
                    <div class="rounded-lg border border-[#E6EBF4] bg-white px-4 py-3">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-[#0F141E]/60">{{ __('messages.users_caste') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-[#080D21]">{{ $user->caste ?? '—' }}</dd>
                    </div>
                    <div class="rounded-lg border border-[#E6EBF4] bg-white px-4 py-3">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-[#0F141E]/60">{{ __('messages.users_house_type') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-[#080D21]">{{ $user->house_type?->label() ?? '—' }}</dd>
                    </div>
                    <div class="rounded-lg border border-[#E6EBF4] bg-white px-4 py-3">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-[#0F141E]/60">{{ __('messages.users_house_number') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-[#080D21]">{{ $user->house_number ?? '—' }}</dd>
                    </div>
                    <div class="rounded-lg border border-[#E6EBF4] bg-white px-4 py-3">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-[#0F141E]/60">{{ __('messages.users_role') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-[#080D21]">{{ $user->roleLabelWithShortForm() }}</dd>
                    </div>
                </dl>
            </div>

            <div class="flex justify-end">
                <x-common.button type="submit">
                    {{ __('messages.profile_save_changes') }}
                </x-common.button>
            </div>
        </form>
    </div>
</x-layouts.admin>
