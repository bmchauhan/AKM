@props([
    'label' => '',
    'name' => '',
    'required' => false,
    'options' => [],
])

@php
    $fieldError = $errors->first($name);
    $hasError = filled($fieldError);
    $selected = old($name, $attributes->get('value', ''));
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

    <select
        name="{{ $name }}"
        id="{{ $name }}"
        @if ($required) required @endif
        {{ $attributes->except('value')->merge([
            'class' => 'w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-[#0F141E] shadow-sm transition focus:outline-none focus:ring-2 ' . ($hasError
                ? 'border-[#AB1E23] focus:border-[#AB1E23] focus:ring-[#AB1E23]/20'
                : 'border-[#E6EBF4] focus:border-[#AB1E23] focus:ring-[#AB1E23]/20'),
        ]) }}
    >
        {{ $slot }}
        @foreach ($options as $option)
            @php
                $optionValue = is_array($option) ? ($option['value'] ?? '') : $option;
                $optionLabel = is_array($option) ? ($option['label'] ?? $optionValue) : $option;
            @endphp
            <option value="{{ $optionValue }}" @selected((string) $selected === (string) $optionValue)>
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>

    @if ($hasError)
        <p class="mt-1.5 text-sm font-medium text-[#AB1E23]">{{ $fieldError }}</p>
    @endif
</div>
