@props([
    'href' => null,
    'type' => 'button',
    'variant' => 'default',
    'title' => '',
])

@php
    $variants = [
        'default' => 'border-[#E6EBF4] text-[#080D21] hover:border-[#E6C280] hover:bg-[#ECEAE1]',
        'accent' => 'border-[#E6C280]/60 text-[#080D21] hover:border-[#E6C280] hover:bg-[#ECEAE1]',
        'danger' => 'border-[#E5989B]/40 text-[#AB1E23] hover:bg-[#E5989B]/15',
    ];
    $classes = 'relative inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border transition ' . ($variants[$variant] ?? $variants['default']);
@endphp

@if ($href)
    <a
        href="{{ $href }}"
        title="{{ $title }}"
        aria-label="{{ $title }}"
        {{ $attributes->merge(['class' => $classes]) }}
    >
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        title="{{ $title }}"
        aria-label="{{ $title }}"
        {{ $attributes->merge(['class' => $classes]) }}
    >
        {{ $slot }}
    </button>
@endif
