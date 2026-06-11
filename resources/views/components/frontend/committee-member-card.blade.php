@props([
    'name' => '',
    'position' => '',
    'image' => '',
    'chairman' => false,
])

<article
    {{ $attributes->merge([
        'class' => 'group flex w-56 shrink-0 snap-center flex-col items-center rounded-2xl border border-[#E6EBF4] bg-[#ECEAE1]/40 p-5 text-center shadow-sm transition hover:border-[#E6C280]/60 hover:bg-white hover:shadow-md sm:w-60',
    ]) }}
>
    <div class="relative">
        <div class="h-28 w-28 overflow-hidden rounded-2xl border-2 border-[#E6C280]/40 bg-[#E6EBF4] shadow-inner sm:h-32 sm:w-32">
            <img
                src="{{ $image }}"
                alt="{{ $name }}"
                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                loading="lazy"
            />
        </div>
        @if ($chairman)
            <span class="absolute -bottom-2 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-full bg-[#AB1E23] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-[#E6EBF4]">
                {{ __('messages.committee_chairman_badge') }}
            </span>
        @endif
    </div>

    <h3 class="mt-5 text-sm font-bold leading-snug text-[#080D21] group-hover:text-[#AB1E23] sm:text-base">
        {{ $name }}
    </h3>
    <p class="mt-1 text-xs font-medium text-[#AB1E23] sm:text-sm">
        {{ $position }}
    </p>
</article>
