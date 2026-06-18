@props([
    'contact' => null,
    'activeTab' => 'services',
    'activeRoleId' => null,
    'supportsCommitteeTab' => false,
    'committeeRoleId' => null,
    'committeeMemberOptions' => [],
    'sourceOptions' => [],
    'showStatusField' => false,
])

@php
    use App\Enums\UsefulDirectorySource;

    $defaultSource = $supportsCommitteeTab
        ? UsefulDirectorySource::CommitteeMember->value
        : UsefulDirectorySource::Manual->value;

    $sourceValue = old('source', $contact?->source?->value ?? $defaultSource);
    $roleIdValue = old('directory_role_id', $contact?->directory_role_id ?? $activeRoleId);
    $titleValue = old('title', $contact?->title);
    $contactNameValue = old('contact_name', $contact?->contact_name);
    $phonePrimaryValue = old('phone_primary', $contact?->phone_primary);
    $phoneSecondaryValue = old('phone_secondary', $contact?->phone_secondary);
    $notesValue = old('notes', $contact?->notes);
    $sortOrderValue = old('sort_order', $contact?->sort_order ?? 0);
    $userIdValue = old('user_id', $contact?->user_id);
    $isActive = old('is_active', $contact?->is_active ?? true);
@endphp

<div
    class="space-y-5"
    x-data="{
        source: @js($sourceValue),
        committeeValue: @js(UsefulDirectorySource::CommitteeMember->value),
        committeeRoleId: @js($committeeRoleId),
        isManual() { return this.source !== this.committeeValue; },
        isCommittee() { return this.source === this.committeeValue; },
        onSourceChange() {
            if (this.isCommittee() && this.committeeRoleId) {
                this.$refs.roleIdInput.value = this.committeeRoleId;
            }
        },
    }"
>
    <input type="hidden" name="directory_role_id" x-ref="roleIdInput" value="{{ $roleIdValue }}">

    @if ($supportsCommitteeTab)
        <x-common.select
            name="source"
            :label="__('messages.useful_directory_entry_type')"
            :options="$sourceOptions"
            :value="$sourceValue"
            x-model="source"
            @change="onSourceChange()"
            required
        />
    @else
        <input type="hidden" name="source" value="{{ UsefulDirectorySource::Manual->value }}">
    @endif

    <template x-if="isCommittee()">
        <div class="space-y-5">
            <x-common.select name="user_id" :label="__('messages.useful_directory_committee_member')" :value="$userIdValue" required>
                <option value="">{{ __('messages.useful_directory_select_committee_member') }}</option>
                @foreach ($committeeMemberOptions as $member)
                    <option value="{{ $member['id'] }}" @selected((string) $userIdValue === (string) $member['id'])>
                        {{ $member['name'] }} — {{ $member['position'] }}@if ($member['phone']) ({{ $member['phone'] }})@endif
                    </option>
                @endforeach
            </x-common.select>

            <x-common.input
                name="title"
                :label="__('messages.useful_directory_display_label')"
                :value="$titleValue"
                :placeholder="__('messages.useful_directory_display_label_hint')"
            />

            <p class="rounded-lg border border-[#E6C280]/40 bg-[#E6EBF4]/40 px-4 py-3 text-xs leading-relaxed text-[#0F141E]/70">
                {{ __('messages.useful_directory_committee_link_hint') }}
            </p>
        </div>
    </template>

    <template x-if="isManual()">
        <div class="space-y-5">
            <x-common.input
                name="title"
                :label="__('messages.useful_directory_service_title')"
                :value="$titleValue"
                :placeholder="__('messages.useful_directory_service_title_placeholder')"
                required
            />

            <x-common.input
                name="contact_name"
                :label="__('messages.useful_directory_contact_name')"
                :value="$contactNameValue"
                required
            />

            <div class="grid gap-5 sm:grid-cols-2">
                <x-common.input
                    name="phone_primary"
                    :label="__('messages.useful_directory_phone_primary')"
                    :value="$phonePrimaryValue"
                    required
                />
                <x-common.input
                    name="phone_secondary"
                    :label="__('messages.useful_directory_phone_secondary')"
                    :value="$phoneSecondaryValue"
                />
            </div>
        </div>
    </template>

    <div class="grid gap-5 sm:grid-cols-2">
        <x-common.input
            type="number"
            name="sort_order"
            :label="__('messages.useful_directory_sort_order')"
            :value="$sortOrderValue"
            min="0"
        />
    </div>

    @if ($showStatusField)
        <label class="flex items-center gap-3 rounded-lg border border-[#E6EBF4] bg-[#E6EBF4]/30 px-4 py-3">
            <input
                type="checkbox"
                name="is_active"
                value="1"
                @checked($isActive)
                class="h-4 w-4 rounded border-[#E6EBF4] text-[#AB1E23] focus:ring-[#AB1E23]/20"
            />
            <span class="text-sm font-medium text-[#080D21]">{{ __('messages.useful_directory_active') }}</span>
        </label>
    @endif

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-[#080D21]">{{ __('messages.finance_notes') }}</label>
        <textarea
            name="notes"
            rows="2"
            class="w-full rounded-lg border border-[#E6EBF4] bg-white px-4 py-2.5 text-sm text-[#0F141E] shadow-sm transition placeholder:text-[#0F141E]/40 focus:border-[#AB1E23] focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20"
            placeholder="{{ __('messages.useful_directory_notes_placeholder') }}"
        >{{ $notesValue }}</textarea>
        @error('notes')
            <p class="mt-1.5 text-sm font-medium text-[#AB1E23]">{{ $message }}</p>
        @enderror
    </div>
</div>
