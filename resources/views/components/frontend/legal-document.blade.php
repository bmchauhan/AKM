@props([
    'updatedKey',
    'sectionsKey',
])

<section class="bg-[#ECEAE1] py-12 sm:py-16">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <p class="text-sm text-[#0F141E]/60">{{ __($updatedKey) }}</p>

        <div class="mt-8 space-y-6">
            @foreach (__($sectionsKey) as $section)
                <article class="rounded-2xl border border-[#E6EBF4] bg-white p-6 shadow-sm sm:p-8">
                    <h2 class="text-xl font-semibold text-[#080D21]">{{ $section['title'] }}</h2>
                    <p class="mt-3 text-base leading-relaxed text-[#0F141E]/80">{{ $section['body'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
