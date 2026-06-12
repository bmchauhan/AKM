@props([
    'label' => '',
    'name' => '',
    'options' => [],
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'submitOnChange' => false,
])

@php
    $fieldError = $errors->first($name);
    $hasError = filled($fieldError);
    $resolvedPlaceholder = $placeholder ?: __('messages.users_select_option');
    $normalizedOptions = collect($options)->map(fn ($option) => [
        'value' => (string) (is_array($option) ? ($option['value'] ?? '') : $option),
        'label' => (string) (is_array($option) ? ($option['label'] ?? '') : $option),
    ])->values()->all();
@endphp

<div
    x-data="searchableSelect({
        name: @js($name),
        options: @js($normalizedOptions),
        selected: @js((string) old($name, $value)),
        placeholder: @js($resolvedPlaceholder),
        searchPlaceholder: @js(__('messages.searchable_select_search')),
        noResultsText: @js(__('messages.searchable_select_no_results')),
        submitOnChange: @js((bool) $submitOnChange),
    })"
    {{ $attributes->class('relative') }}
    @keydown.escape.window="close()"
>
    @if ($label)
        <label class="mb-1.5 block text-sm font-semibold text-[#080D21]">
            {{ $label }}
            @if ($required)
                <span class="text-[#AB1E23]">*</span>
            @endif
        </label>
    @endif

    <input type="hidden" name="{{ $name }}" x-ref="hiddenInput" :value="selected">

    <button
        type="button"
        @click="toggle()"
        :aria-expanded="open"
        class="flex w-full items-center justify-between rounded-lg border bg-white px-3 py-2.5 text-left text-sm shadow-sm transition focus:outline-none focus:ring-2 {{ $hasError ? 'border-[#AB1E23] focus:border-[#AB1E23] focus:ring-[#AB1E23]/20' : 'border-[#E6EBF4] focus:border-[#AB1E23] focus:ring-[#AB1E23]/20' }}"
    >
        <span class="truncate" :class="selectedLabel ? 'text-[#0F141E]' : 'text-[#0F141E]/40'" x-text="selectedLabel || placeholder"></span>
        <svg class="h-4 w-4 shrink-0 text-[#080D21]/50 transition" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <div
        x-show="open"
        x-cloak
        @click.outside="close()"
        class="absolute z-50 mt-1 w-full overflow-hidden rounded-lg border border-[#E6EBF4] bg-white shadow-lg"
    >
        <div class="border-b border-[#E6EBF4] p-2">
            <input
                x-ref="searchInput"
                type="text"
                x-model="search"
                :placeholder="searchPlaceholder"
                class="w-full rounded-md border border-[#E6EBF4] bg-white px-3 py-2 text-sm text-[#0F141E] placeholder:text-[#0F141E]/40 focus:border-[#AB1E23] focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20"
            >
        </div>

        <ul class="max-h-60 overflow-y-auto py-1" role="listbox">
            <template x-if="filteredOptions.length === 0">
                <li class="px-3 py-2 text-sm text-[#0F141E]/50" x-text="noResultsText"></li>
            </template>
            <template x-for="option in filteredOptions" :key="option.value">
                <li>
                    <button
                        type="button"
                        @click="selectOption(option)"
                        class="flex w-full px-3 py-2 text-left text-sm transition hover:bg-[#ECEAE1]"
                        :class="String(option.value) === selected ? 'bg-[#E6EBF4] font-medium text-[#080D21]' : 'text-[#0F141E]'"
                        x-text="option.label"
                    ></button>
                </li>
            </template>
        </ul>
    </div>

    @if ($hasError)
        <p class="mt-1.5 text-sm font-medium text-[#AB1E23]">{{ $fieldError }}</p>
    @endif
</div>
