@props([
    'name' => '',
    'position' => '',
    'image' => '',
    'hasPhoto' => true,
    'isChief' => false,
    'isLeadership' => false,
    'bio' => null,
    'variant' => 'carousel',
])

@php
    $isGrid = $variant === 'grid';
@endphp

<article
    {{ $attributes->merge([
        'class' => $isGrid
            ? 'group flex flex-col items-center rounded-2xl border border-[#E6EBF4] bg-white p-6 text-center shadow-sm transition hover:border-[#E6C280]/60 hover:shadow-md'
            : 'group flex w-56 shrink-0 snap-center flex-col items-center rounded-2xl border border-[#E6EBF4] bg-[#ECEAE1]/40 p-5 text-center shadow-sm transition hover:border-[#E6C280]/60 hover:bg-white hover:shadow-md sm:w-60',
    ]) }}
    @class([
        'ring-2 ring-[#AB1E23]/20' => $isLeadership && $isGrid,
    ])
>
    <div class="relative">
        <div @class([
            'overflow-hidden rounded-2xl border-2 border-[#E6C280]/40 bg-[#E6EBF4] shadow-inner',
            'h-28 w-28 sm:h-32 sm:w-32' => ! $isGrid,
            'h-32 w-32 sm:h-36 sm:w-36' => $isGrid,
        ])>
            <img
                src="{{ $image }}"
                alt="{{ $name }}"
                @class([
                    'h-full w-full transition duration-300 group-hover:scale-105',
                    'object-cover' => $hasPhoto,
                    'object-contain p-3' => ! $hasPhoto,
                ])
                loading="lazy"
            />
        </div>
        @if ($isChief)
            <span class="absolute -bottom-2 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-full bg-[#AB1E23] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-[#E6EBF4]">
                {{ __('messages.committee_chairman_badge') }}
            </span>
        @endif
    </div>

    <h3 @class([
        'font-bold leading-snug text-[#080D21] transition group-hover:text-[#AB1E23]',
        'mt-5 text-sm sm:text-base' => ! $isGrid,
        'mt-6 text-base sm:text-lg' => $isGrid,
    ])>
        {{ $name }}
    </h3>

    <p @class([
        'mt-1 font-medium text-[#AB1E23]',
        'text-xs sm:text-sm' => ! $isGrid,
        'text-sm' => $isGrid,
    ])>
        {{ $position }}
    </p>

    @if ($isGrid && filled($bio))
        <p class="mt-3 text-xs leading-relaxed text-[#0F141E]/65">
            {{ $bio }}
        </p>
    @endif
</article>
