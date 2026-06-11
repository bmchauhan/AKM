@props([
    'label' => '',
    'name' => '',
    'accept' => '',
    'hint' => '',
    'currentUrl' => null,
    'currentLabel' => null,
])

@php
    $fieldError = $errors->first($name);
    $hasError = filled($fieldError);
@endphp

<div>
    @if ($label)
        <label for="{{ $name }}" class="mb-1.5 block text-sm font-semibold text-[#080D21]">
            {{ $label }}
        </label>
    @endif

    @if ($currentUrl)
        <div class="mb-2 flex items-center gap-2 text-sm text-[#0F141E]/70">
            <span>{{ $currentLabel ?? __('messages.users_current_file') }}:</span>
            <a href="{{ $currentUrl }}" target="_blank" class="font-medium text-[#AB1E23] hover:underline">
                {{ __('messages.users_view_file') }}
            </a>
        </div>
    @endif

    <input
        type="file"
        name="{{ $name }}"
        id="{{ $name }}"
        @if ($accept) accept="{{ $accept }}" @endif
        {{ $attributes->merge([
            'class' => 'block w-full cursor-pointer rounded-lg border bg-white px-3 py-2 text-sm text-[#0F141E] file:mr-3 file:rounded-md file:border-0 file:bg-[#E6EBF4] file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-[#080D21] focus:outline-none focus:ring-2 ' . ($hasError
                ? 'border-[#AB1E23] focus:ring-[#AB1E23]/20'
                : 'border-[#E6EBF4] focus:ring-[#AB1E23]/20'),
        ]) }}
    />

    @if ($hint)
        <p class="mt-1 text-xs text-[#0F141E]/50">{{ $hint }}</p>
    @endif

    @if ($hasError)
        <p class="mt-1.5 text-sm font-medium text-[#AB1E23]">{{ $fieldError }}</p>
    @endif
</div>
