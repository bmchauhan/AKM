@props([
    'label' => '',
    'value' => '',
    'hint' => null,
    'tone' => 'neutral',
    'trend' => null,
])

@php
    $toneClasses = match ($tone) {
        'primary' => 'border-[#080D21]/15 bg-gradient-to-br from-white to-[#E6EBF4]/40 ring-1 ring-[#080D21]/5',
        'income' => 'border-[#E6C280]/50 bg-gradient-to-br from-white to-[#E6C280]/15',
        'expense' => 'border-[#AB1E23]/20 bg-gradient-to-br from-white to-[#E5989B]/12',
        'danger' => 'border-[#AB1E23]/35 bg-gradient-to-br from-white to-[#E5989B]/20',
        'accent' => 'border-[#E6C280]/40 bg-gradient-to-br from-[#E6EBF4]/50 to-white',
        default => 'border-[#E6EBF4] bg-white',
    };

    $valueClasses = match ($tone) {
        'danger', 'expense' => 'text-[#AB1E23]',
        'income' => 'text-[#080D21]',
        'primary' => 'text-[#080D21]',
        default => 'text-[#080D21]',
    };
@endphp

<div {{ $attributes->merge(['class' => "rounded-xl border p-5 shadow-sm sm:p-6 {$toneClasses}"]) }}>
    <div class="flex items-start justify-between gap-3">
        <p class="text-xs font-semibold uppercase tracking-wide text-[#0F141E]/50">{{ $label }}</p>
        @if (is_array($trend) && filled($trend['label'] ?? null))
            <span @class([
                'inline-flex shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide',
                'bg-[#E6C280]/30 text-[#080D21]' => ($trend['direction'] ?? '') === 'up',
                'bg-[#E5989B]/25 text-[#AB1E23]' => ($trend['direction'] ?? '') === 'down',
            ])>
                {{ $trend['label'] }}
            </span>
        @endif
    </div>
    <p class="mt-3 text-2xl font-bold leading-tight sm:text-[1.65rem] {{ $valueClasses }}">{{ $value }}</p>
    @if (filled($hint))
        <p class="mt-2 text-xs leading-relaxed text-[#0F141E]/60">{{ $hint }}</p>
    @endif
</div>
