@php
    $images = [
        ['src' => 'https://picsum.photos/seed/clubhouse/800/600', 'label' => __('messages.facility_clubhouse'), 'span' => 'col-span-2 row-span-2'],
        ['src' => 'https://picsum.photos/seed/pool/600/800', 'label' => __('messages.facility_pool'), 'span' => 'row-span-2'],
        ['src' => 'https://picsum.photos/seed/garden/600/400', 'label' => __('messages.facility_garden'), 'span' => ''],
        ['src' => 'https://picsum.photos/seed/playground/600/400', 'label' => __('messages.facility_playground'), 'span' => ''],
        ['src' => 'https://picsum.photos/seed/gym/800/500', 'label' => __('messages.facility_gym'), 'span' => 'col-span-2'],
        ['src' => 'https://picsum.photos/seed/parking/600/400', 'label' => __('messages.facility_parking'), 'span' => ''],
        ['src' => 'https://picsum.photos/seed/temple/600/800', 'label' => __('messages.facility_temple'), 'span' => 'row-span-2'],
        ['src' => 'https://picsum.photos/seed/lawn/600/400', 'label' => __('messages.facility_lawn'), 'span' => ''],
        ['src' => 'https://picsum.photos/seed/gate/800/600', 'label' => __('messages.facility_gate'), 'span' => 'col-span-2'],
        ['src' => 'https://picsum.photos/seed/walktrack/600/400', 'label' => __('messages.facility_walktrack'), 'span' => ''],
        ['src' => 'https://picsum.photos/seed/library/600/400', 'label' => __('messages.facility_library'), 'span' => ''],
        ['src' => 'https://picsum.photos/seed/games/600/800', 'label' => __('messages.facility_games'), 'span' => 'row-span-2'],
    ];
@endphp

<section
    id="facilities-gallery"
    data-section="facilities-gallery"
    class="scroll-mt-24 w-full bg-[#ECEAE1] py-12 sm:py-16"
>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#AB1E23]">
                {{ __('messages.facilities_gallery_eyebrow') }}
            </p>
            <h2 class="mt-2 text-3xl font-bold text-[#080D21] sm:text-4xl">
                {{ __('messages.facilities_gallery_title') }}
            </h2>
            <p class="mt-3 text-base leading-relaxed text-[#0F141E]">
                {{ __('messages.facilities_gallery_subtitle') }}
            </p>
        </div>

        <div class="mt-10 grid auto-rows-[140px] grid-cols-2 gap-3 sm:auto-rows-[160px] md:grid-cols-4 md:gap-4">
            @foreach ($images as $image)
                <figure
                    @class([
                        'group relative overflow-hidden rounded-2xl border border-[#E6EBF4] bg-white shadow-sm transition hover:border-[#E6C280]/60 hover:shadow-md',
                        $image['span'],
                    ])
                >
                    <img
                        src="{{ $image['src'] }}"
                        alt="{{ $image['label'] }}"
                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                        loading="lazy"
                    />
                    <figcaption
                        class="absolute inset-0 flex items-end bg-gradient-to-t from-[#080D21]/80 via-[#080D21]/20 to-transparent p-3 opacity-90 transition group-hover:opacity-100 sm:p-4"
                    >
                        <span class="text-xs font-semibold text-[#E6EBF4] sm:text-sm">
                            {{ $image['label'] }}
                        </span>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
