@props([
    'type' => 'news',
    'date' => '',
    'title' => '',
    'excerpt' => '',
])

@php
    $badges = [
        'news' => 'bg-[#E6EBF4] text-[#080D21] ring-[#080D21]/10',
        'event' => 'bg-[#E6C280]/25 text-[#080D21] ring-[#E6C280]/50',
        'notice' => 'bg-[#E5989B]/25 text-[#AB1E23] ring-[#AB1E23]/20',
    ];
    $badgeClass = $badges[$type] ?? $badges['news'];
    $typeLabel = __('messages.updates_type_' . $type);
@endphp

<article
    {{ $attributes->merge(['class' => 'group flex flex-col rounded-xl border border-[#E6EBF4] bg-white p-5 shadow-sm transition hover:border-[#E6C280]/60 hover:shadow-md']) }}
    data-update-type="{{ $type }}"
>
    <div class="flex items-center justify-between gap-3">
        <span @class(['inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset', $badgeClass])>
            {{ $typeLabel }}
        </span>
        <time class="shrink-0 text-xs font-medium text-[#0F141E]/60">{{ $date }}</time>
    </div>

    <h3 class="mt-4 text-base font-semibold leading-snug text-[#080D21] group-hover:text-[#AB1E23]">
        {{ $title }}
    </h3>

    <p class="mt-2 flex-1 text-sm leading-relaxed text-[#0F141E]">
        {{ $excerpt }}
    </p>

    <a
        href="#"
        class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-[#AB1E23] transition hover:text-[#080D21]"
    >
        {{ __('messages.read_more') }}
        <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </a>
</article>
