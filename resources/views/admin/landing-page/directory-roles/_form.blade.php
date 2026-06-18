@props([
    'role' => null,
    'showStatusField' => false,
    'showSlugField' => false,
])

@php
    $slugValue = old('slug', $role?->slug);
    $nameEnValue = old('name_en', $role?->name_en);
    $nameHiValue = old('name_hi', $role?->name_hi);
    $nameGuValue = old('name_gu', $role?->name_gu);
    $sortOrderValue = old('sort_order', $role?->sort_order ?? 0);
    $supportsCommittee = old('supports_committee_link', $role?->supports_committee_link ?? false);
    $isActive = old('is_active', $role?->is_active ?? true);
@endphp

<div class="space-y-5">
    @if ($showSlugField)
        <x-common.input
            name="slug"
            :label="__('messages.directory_roles_slug')"
            :value="$slugValue"
            :placeholder="__('messages.directory_roles_slug_placeholder')"
        />
        <p class="text-xs text-[#0F141E]/60">{{ __('messages.directory_roles_slug_hint') }}</p>
    @endif

    <div class="rounded-xl border border-[#E6C280]/40 bg-[#E6EBF4]/30 p-4 space-y-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-[#080D21]">{{ __('messages.directory_roles_names_heading') }}</p>
        <p class="text-xs leading-relaxed text-[#0F141E]/65">{{ __('messages.directory_roles_names_hint') }}</p>

        <x-common.input
            name="name_en"
            :label="__('messages.directory_roles_name_en')"
            :value="$nameEnValue"
            required
        />
        <x-common.input
            name="name_hi"
            :label="__('messages.directory_roles_name_hi')"
            :value="$nameHiValue"
        />
        <x-common.input
            name="name_gu"
            :label="__('messages.directory_roles_name_gu')"
            :value="$nameGuValue"
        />
    </div>

    <x-common.input
        type="number"
        name="sort_order"
        :label="__('messages.useful_directory_sort_order')"
        :value="$sortOrderValue"
        min="0"
    />

    <label class="flex items-start gap-3 rounded-lg border border-[#E6EBF4] bg-[#E6EBF4]/30 px-4 py-3">
        <input
            type="checkbox"
            name="supports_committee_link"
            value="1"
            @checked($supportsCommittee)
            class="mt-0.5 h-4 w-4 rounded border-[#E6EBF4] text-[#AB1E23] focus:ring-[#AB1E23]/20"
        />
        <span>
            <span class="block text-sm font-medium text-[#080D21]">{{ __('messages.directory_roles_supports_committee') }}</span>
            <span class="mt-1 block text-xs leading-relaxed text-[#0F141E]/65">{{ __('messages.directory_roles_supports_committee_hint') }}</span>
        </span>
    </label>

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
</div>
