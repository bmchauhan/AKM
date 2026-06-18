@props([
    'eyebrow' => '',
    'title' => '',
    'subtitle' => null,
])

<section class="border-b border-[#E6C280]/40 bg-gradient-to-r from-[#E6EBF4]/90 via-white to-[#ECEAE1]/80">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-12 lg:px-8">
        @if (filled($eyebrow))
            <p class="inline-flex rounded-full bg-[#AB1E23]/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-[#AB1E23] sm:text-sm">
                {{ $eyebrow }}
            </p>
        @endif

        @if (filled($title))
            <h1 class="mt-4 text-3xl font-bold leading-tight text-[#080D21] sm:text-4xl">
                {{ $title }}
            </h1>
        @endif

        @if (filled($subtitle))
            <p class="mt-3 max-w-2xl text-base leading-relaxed text-[#0F141E]/75">
                {{ $subtitle }}
            </p>
        @endif
    </div>
</section>
