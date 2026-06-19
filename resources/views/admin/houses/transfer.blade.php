<x-layouts.admin :pageTitle="__('messages.houses_transfer')">
    <div class="space-y-6">
        <div>
            <a href="{{ route('admin.houses.show', $house) }}" class="text-xs font-medium text-[#AB1E23] hover:underline">&larr; {{ __('messages.houses_back') }}</a>
            <h2 class="mt-2 text-xl font-bold text-[#080D21]">
                {{ $currentOwner ? __('messages.houses_transfer_title', ['house' => $house->label()]) : __('messages.houses_assign_owner_title', ['house' => $house->label()]) }}
            </h2>
            <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.houses_transfer_subtitle') }}</p>
        </div>

        @if ($currentOwner)
            <div class="rounded-xl border border-[#E5989B]/40 bg-[#E5989B]/10 px-4 py-3 text-sm text-[#080D21]">
                {{ __('messages.houses_transfer_current_owner', ['name' => $currentOwner->fullName()]) }}
                @if ($currentOwner->hasCommitteeRole())
                    <p class="mt-2 text-xs text-[#0F141E]/80">{{ __('messages.houses_transfer_committee_role_notice') }}</p>
                @endif
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-[#AB1E23]/40 bg-[#E5989B]/15 px-4 py-3 text-sm text-[#080D21]">
                <p class="font-semibold">{{ __('messages.houses_transfer_form_errors') }}</p>
                <ul class="mt-2 list-inside list-disc space-y-1 text-[#0F141E]/80">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('admin.houses.transfer.store', $house) }}"
            enctype="multipart/form-data"
            class="space-y-6"
            data-confirm-delete
            data-confirm-title="{{ __('messages.houses_transfer_confirm_title') }}"
            data-confirm-message="{{ __('messages.houses_transfer_confirm_text') }}"
            data-confirm-yes="{{ __('messages.houses_transfer_confirm_yes') }}"
            data-confirm-cancel="{{ __('messages.delete_confirm_cancel') }}"
        >
            @csrf

            <section class="rounded-xl border border-[#E6EBF4] bg-white p-5 shadow-sm">
                <h3 class="text-sm font-bold uppercase tracking-wide text-[#080D21]">{{ __('messages.houses_transfer_details') }}</h3>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <x-common.input
                        type="date"
                        name="effective_date"
                        :label="__('messages.houses_transfer_effective_date')"
                        :value="old('effective_date', now()->toDateString())"
                        required
                    />

                    <x-common.select
                        name="transfer_type"
                        :label="__('messages.houses_transfer_type')"
                        :options="collect($transferTypes)->map(fn ($t) => ['value' => $t->value, 'label' => $t->label()])->all()"
                        :value="old('transfer_type', 'sale')"
                        required
                    >
                        <option value="">{{ __('messages.users_select_option') }}</option>
                    </x-common.select>
                </div>

                <div class="mt-4">
                    <label for="notes" class="mb-1 block text-sm font-medium text-[#080D21]">{{ __('messages.houses_transfer_notes') }}</label>
                    <textarea
                        id="notes"
                        name="notes"
                        rows="3"
                        class="w-full rounded border border-[#E6EBF4] bg-[#ECEAE1]/30 px-3 py-2 text-sm text-[#0F141E] focus:border-[#AB1E23] focus:outline-none focus:ring-1 focus:ring-[#AB1E23]"
                    >{{ old('notes') }}</textarea>
                </div>
            </section>

            <section class="rounded-xl border border-[#E6EBF4] bg-white p-5 shadow-sm">
                <h3 class="text-sm font-bold uppercase tracking-wide text-[#080D21]">{{ __('messages.houses_new_owner') }}</h3>
                <p class="mt-1 text-xs text-[#0F141E]/60">{{ __('messages.houses_new_owner_hint', ['house' => $house->label()]) }}</p>

                <div class="mt-4 space-y-4">
                    <div class="grid gap-4 sm:grid-cols-3">
                        <x-common.input name="first_name" :label="__('messages.users_first_name')" :value="old('first_name')" required />
                        <x-common.input name="middle_name" :label="__('messages.users_middle_name')" :value="old('middle_name')" />
                        <x-common.input name="last_name" :label="__('messages.users_last_name')" :value="old('last_name')" required />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-common.input name="caste" :label="__('messages.users_caste')" :value="old('caste')" />
                        <x-common.select
                            name="gender"
                            :label="__('messages.users_gender')"
                            :options="collect(\App\Enums\Gender::cases())->map(fn ($g) => ['value' => $g->value, 'label' => $g->label()])->all()"
                            :value="old('gender')"
                            required
                        >
                            <option value="">{{ __('messages.users_select_option') }}</option>
                        </x-common.select>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-common.input name="mobile_number" :label="__('messages.users_mobile')" :value="old('mobile_number')" required />
                        <x-common.input name="alternate_number" :label="__('messages.users_alternate_mobile')" :value="old('alternate_number')" />
                    </div>

                    <x-common.input name="email" type="email" :label="__('messages.users_email')" :value="old('email')" required />

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="id_proof" class="mb-1 block text-sm font-medium text-[#080D21]">{{ __('messages.users_id_proof') }}</label>
                            <input type="file" id="id_proof" name="id_proof" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-sm text-[#0F141E]" />
                        </div>
                        <div>
                            <label for="profile_image" class="mb-1 block text-sm font-medium text-[#080D21]">{{ __('messages.users_profile_image') }}</label>
                            <input type="file" id="profile_image" name="profile_image" accept=".jpg,.jpeg,.png,.webp" class="block w-full text-sm text-[#0F141E]" />
                        </div>
                    </div>
                </div>
            </section>

            <div class="flex flex-wrap gap-3">
                <x-common.button type="submit">{{ __('messages.houses_transfer_submit') }}</x-common.button>
                <a href="{{ route('admin.houses.show', $house) }}" class="rounded px-4 py-2 text-sm font-medium text-[#0F141E]/70 transition hover:text-[#080D21]">
                    {{ __('messages.delete_confirm_cancel') }}
                </a>
            </div>
        </form>
    </div>
</x-layouts.admin>
