@props([
    'label' => '',
    'name' => '',
    'type' => 'text',
    'placeholder' => '',
    'required' => false,
])

@php
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

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        aria-invalid="{{ $hasError ? 'true' : 'false' }}"
        @if ($hasError) aria-describedby="{{ $name }}-error" @endif
        {{ $attributes->merge([
            'class' => 'w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-[#0F141E] shadow-sm transition placeholder:text-[#0F141E]/40 focus:outline-none focus:ring-2 ' . ($hasError
                ? 'border-[#AB1E23] focus:border-[#AB1E23] focus:ring-[#AB1E23]/20'
                : 'border-[#E6EBF4] focus:border-[#AB1E23] focus:ring-[#AB1E23]/20'),
        ]) }}
    />

    @if ($hasError)
        <p id="{{ $name }}-error" class="mt-1.5 text-sm font-medium text-[#AB1E23]">{{ $fieldError }}</p>
    @endif
</div>
