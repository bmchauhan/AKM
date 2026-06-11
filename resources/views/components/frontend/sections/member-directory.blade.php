@php
    $listings = [
        ['category' => 'emergency', 'name' => __('messages.directory_police'), 'subtitle' => __('messages.directory_police_sub'), 'phone' => '100', 'featured' => false],
        ['category' => 'emergency', 'name' => __('messages.directory_ambulance'), 'subtitle' => __('messages.directory_ambulance_sub'), 'phone' => '108', 'featured' => false],
        ['category' => 'emergency', 'name' => __('messages.directory_fire'), 'subtitle' => __('messages.directory_fire_sub'), 'phone' => '101', 'featured' => false],
        ['category' => 'emergency', 'name' => __('messages.directory_hospital'), 'subtitle' => __('messages.directory_hospital_sub'), 'phone' => '+91 261 240 1234', 'featured' => false],

        ['category' => 'doctors', 'name' => 'Dr. Ramesh Patel', 'subtitle' => __('messages.directory_dr_general'), 'phone' => '+91 98765 11101', 'featured' => true],
        ['category' => 'doctors', 'name' => 'Dr. Sneha Shah', 'subtitle' => __('messages.directory_dr_pediatric'), 'phone' => '+91 98765 11102', 'featured' => false],
        ['category' => 'doctors', 'name' => 'Dr. Anil Mehta', 'subtitle' => __('messages.directory_dr_cardio'), 'phone' => '+91 98765 11103', 'featured' => false],

        ['category' => 'plumbers', 'name' => 'Jayesh Plumbing', 'subtitle' => __('messages.directory_plumber_24x7'), 'phone' => '+91 98765 22201', 'featured' => true],
        ['category' => 'plumbers', 'name' => 'Krishna Pipe Works', 'subtitle' => __('messages.directory_plumber_home'), 'phone' => '+91 98765 22202', 'featured' => false],

        ['category' => 'electricians', 'name' => 'Raj Electricals', 'subtitle' => __('messages.directory_electrician_home'), 'phone' => '+91 98765 33301', 'featured' => true],
        ['category' => 'electricians', 'name' => 'Shree Power Solutions', 'subtitle' => __('messages.directory_electrician_commercial'), 'phone' => '+91 98765 33302', 'featured' => false],

        ['category' => 'beauty', 'name' => 'Glamour Beauty Salon', 'subtitle' => __('messages.directory_beauty_salon'), 'phone' => '+91 98765 44401', 'featured' => true],
        ['category' => 'beauty', 'name' => "Priya's Parlor", 'subtitle' => __('messages.directory_beauty_home'), 'phone' => '+91 98765 44402', 'featured' => false],

        ['category' => 'officials', 'name' => 'Shri Harishbhai Patel', 'subtitle' => __('messages.directory_sarpanch'), 'phone' => '+91 98765 55501', 'featured' => false],
        ['category' => 'officials', 'name' => 'Shri Dilipbhai Shah', 'subtitle' => __('messages.directory_pramukh'), 'phone' => '+91 98765 55502', 'featured' => false],
        ['category' => 'officials', 'name' => 'Adv. Meera Desai', 'subtitle' => __('messages.directory_advocate'), 'phone' => '+91 98765 55503', 'featured' => true],
        ['category' => 'officials', 'name' => 'Shri Kiran Joshi', 'subtitle' => __('messages.directory_talati'), 'phone' => '+91 98765 55504', 'featured' => false],
        ['category' => 'officials', 'name' => __('messages.directory_police_station'), 'subtitle' => __('messages.directory_police_station_sub'), 'phone' => '+91 261 240 5678', 'featured' => false],
    ];

    $filters = ['all', 'emergency', 'doctors', 'plumbers', 'electricians', 'beauty', 'officials'];
@endphp

<section
    id="member-directory"
    data-section="member-directory"
    class="scroll-mt-24 w-full border-y border-[#E6C280]/25 bg-[#ECEAE1] py-12 sm:py-16"
>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#AB1E23]">
                {{ __('messages.directory_eyebrow') }}
            </p>
            <h2 class="mt-2 text-3xl font-bold text-[#080D21] sm:text-4xl">
                {{ __('messages.directory_title') }}
            </h2>
            <p class="mt-3 text-base leading-relaxed text-[#0F141E]">
                {{ __('messages.directory_subtitle') }}
            </p>
        </div>

        <div class="mt-10" x-data="{ filter: 'all' }">
            <div class="flex flex-wrap items-center justify-center gap-2">
                @foreach ($filters as $filter)
                    <button
                        type="button"
                        @click="filter = '{{ $filter }}'"
                        :class="filter === '{{ $filter }}'
                            ? 'bg-[#080D21] text-[#E6EBF4] ring-[#080D21]'
                            : 'bg-white text-[#080D21] ring-[#E6EBF4] hover:ring-[#E6C280]/50'"
                        class="rounded-full px-3 py-1.5 text-xs font-semibold ring-1 transition sm:px-4 sm:text-sm"
                    >
                        {{ __('messages.directory_filter_' . $filter) }}
                    </button>
                @endforeach
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($listings as $listing)
                    <div x-show="filter === 'all' || filter === '{{ $listing['category'] }}'" x-cloak>
                        <x-frontend.directory-listing-card
                            :name="$listing['name']"
                            :subtitle="$listing['subtitle']"
                            :phone="$listing['phone']"
                            :featured="$listing['featured']"
                            class="h-full"
                        />
                    </div>
                @endforeach
            </div>

            <p class="mt-8 text-center text-xs text-[#0F141E]/60 sm:text-sm">
                {{ __('messages.directory_disclaimer') }}
            </p>
        </div>
    </div>
</section>
