@props([
    'title' => '',
    'detail' => '',
    'href' => null,
    'icon' => 'phone',
])

@php
    $icons = [
        'phone' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z',
        'mail' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        'clock' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        'shield' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        'tool' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
    ];
    $iconPath = $icons[$icon] ?? $icons['phone'];
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-4 rounded-xl border border-[#E6EBF4] bg-white p-4 shadow-sm transition hover:border-[#E6C280]/50']) }}>
    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#E6EBF4] text-[#AB1E23]">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}" />
        </svg>
    </div>
    <div class="min-w-0 flex-1">
        <h4 class="text-sm font-semibold text-[#080D21]">{{ $title }}</h4>
        @if ($href)
            <a href="{{ $href }}" class="mt-0.5 block text-sm text-[#AB1E23] transition hover:text-[#080D21]">
                {{ $detail }}
            </a>
        @else
            <p class="mt-0.5 text-sm text-[#0F141E]">{{ $detail }}</p>
        @endif
    </div>
</div>
