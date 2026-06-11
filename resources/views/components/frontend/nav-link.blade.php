@props([
    'href' => '#',
    'active' => false,
    'highlight' => false,
    'section' => null,
])

<a
    href="{{ $href }}"
    @if($section) data-nav-section="{{ $section }}" @endif
    @class([
        'nav-section-link group relative shrink-0 whitespace-nowrap rounded-full px-2 py-1 text-xs font-semibold tracking-wide transition-all duration-200 sm:px-2.5 sm:py-1.5 lg:px-3 lg:text-sm',
        'bg-white text-[#AB1E23] shadow-sm ring-1 ring-[#E6C280]/50' => $active && ! $highlight,
        'bg-[#AB1E23] text-[#E6EBF4] shadow-sm ring-1 ring-[#AB1E23]/30 hover:bg-[#E6C280] hover:text-[#080D21] hover:ring-[#E6C280]/40' => $highlight,
        'text-[#080D21] hover:bg-white hover:text-[#AB1E23] hover:shadow-sm hover:ring-1 hover:ring-[#E6C280]/30' => ! $active && ! $highlight,
    ])
>
    <span class="relative z-10">{{ $slot }}</span>
</a>
