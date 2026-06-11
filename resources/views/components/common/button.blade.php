@props([
    'type' => 'button',
    'variant' => 'primary',
    'href' => null,
])

@php
    $variants = [
        'primary' => 'bg-[#AB1E23] text-[#E6EBF4] hover:bg-[#E6C280] hover:text-[#080D21] shadow-md ring-2 ring-[#AB1E23]/20 hover:ring-[#E6C280]/40',
        'secondary' => 'bg-white text-[#080D21] border border-[#E6EBF4] hover:border-[#E6C280] hover:bg-[#E6EBF4]',
    ];
    $classes = 'inline-flex items-center justify-center rounded-lg px-5 py-2.5 text-sm font-semibold transition ' . ($variants[$variant] ?? $variants['primary']);
@endphp

@if ($href)
    <a
        href="{{ $href }}"
        {{ $attributes->merge(['class' => $classes]) }}
    >
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        {{ $attributes->merge(['class' => $classes]) }}
    >
        {{ $slot }}
    </button>
@endif
