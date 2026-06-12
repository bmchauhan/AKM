@props([
    'label' => '',
    'value' => '',
    'hint' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-[#E6EBF4] bg-white p-4 shadow-sm sm:p-5']) }}>
    <p class="text-xs font-semibold uppercase tracking-wide text-[#0F141E]/50">{{ $label }}</p>
    <p class="mt-2 text-2xl font-bold text-[#080D21]">{{ $value }}</p>
    @if (filled($hint))
        <p class="mt-1.5 text-xs leading-relaxed text-[#0F141E]/60">{{ $hint }}</p>
    @endif
</div>
