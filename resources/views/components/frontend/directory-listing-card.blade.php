@props([
    'name' => '',
    'subtitle' => '',
    'phone' => '',
    'featured' => false,
])

<article
    {{ $attributes->merge([
        'class' => 'relative flex flex-col rounded-xl border bg-white p-4 shadow-sm transition hover:shadow-md ' . ($featured ? 'border-[#E6C280] ring-1 ring-[#E6C280]/40' : 'border-[#E6EBF4] hover:border-[#E6C280]/50'),
    ]) }}
>
    @if ($featured)
        <span class="absolute -top-2.5 right-3 rounded-full bg-[#E6C280] px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-[#080D21]">
            {{ __('messages.directory_recommended') }}
        </span>
    @endif

    <h4 class="pr-16 text-sm font-bold text-[#080D21]">{{ $name }}</h4>
    <p class="mt-0.5 text-xs text-[#0F141E]/70">{{ $subtitle }}</p>

    <a
        href="tel:{{ preg_replace('/\s+/', '', $phone) }}"
        class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-[#AB1E23] transition hover:text-[#080D21]"
    >
        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
        </svg>
        {{ $phone }}
    </a>
</article>
