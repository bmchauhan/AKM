@props([
    'label' => '',
    'name' => '',
    'placeholder' => '',
    'required' => false,
    'value' => null,
])

@php
    $inputValue = $value ?? '';
    $fieldError = $errors->first($name);
    $hasError = filled($fieldError);
@endphp

<div>
    @if ($label)
        <label for="{{ $name }}" class="mb-1.5 block text-sm font-semibold text-[#080D21]">
            {{ $label }}
            @if ($required)
                <span class="text-[#AB1E23]">*</span>
            @endif
        </label>
    @endif

    <div class="relative" x-data="{ show: false }">
        <input
            :type="show ? 'text' : 'password'"
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ $inputValue }}"
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            aria-invalid="{{ $hasError ? 'true' : 'false' }}"
            @if ($hasError) aria-describedby="{{ $name }}-error" @endif
            {{ $attributes->merge([
                'class' => 'w-full rounded-lg border bg-white py-2.5 pl-4 pr-11 text-sm text-[#0F141E] shadow-sm transition placeholder:text-[#0F141E]/40 focus:outline-none focus:ring-2 ' . ($hasError
                    ? 'border-[#AB1E23] focus:border-[#AB1E23] focus:ring-[#AB1E23]/20'
                    : 'border-[#E6EBF4] focus:border-[#AB1E23] focus:ring-[#AB1E23]/20'),
            ]) }}
        />

        <button
            type="button"
            @click="show = !show"
            class="absolute inset-y-0 right-0 flex items-center rounded-r-lg px-3 text-[#0F141E]/45 transition hover:text-[#AB1E23] focus:outline-none focus-visible:text-[#AB1E23]"
            :aria-label="show ? '{{ __('messages.password_hide') }}' : '{{ __('messages.password_show') }}'"
            :title="show ? '{{ __('messages.password_hide') }}' : '{{ __('messages.password_show') }}'"
        >
            <svg
                x-show="!show"
                x-cloak
                class="h-5 w-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>

            <svg
                x-show="show"
                x-cloak
                class="h-5 w-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
            </svg>
        </button>
    </div>

    @if ($hasError)
        <p id="{{ $name }}-error" class="mt-1.5 text-sm font-medium text-[#AB1E23]">{{ $fieldError }}</p>
    @endif
</div>
