@php
    $members = [
        ['name' => 'Rajesh Mehta', 'position' => __('messages.committee_pos_chairman'), 'image' => 'https://i.pravatar.cc/400?img=12', 'chairman' => true],
        ['name' => 'Priya Shah', 'position' => __('messages.committee_pos_vice_chairman'), 'image' => 'https://i.pravatar.cc/300?img=5'],
        ['name' => 'Amit Patel', 'position' => __('messages.committee_pos_secretary'), 'image' => 'https://i.pravatar.cc/300?img=8'],
        ['name' => 'Kavita Desai', 'position' => __('messages.committee_pos_treasurer'), 'image' => 'https://i.pravatar.cc/300?img=9'],
        ['name' => 'Vikram Joshi', 'position' => __('messages.committee_pos_joint_secretary'), 'image' => 'https://i.pravatar.cc/300?img=11'],
        ['name' => 'Neha Agarwal', 'position' => __('messages.committee_pos_events'), 'image' => 'https://i.pravatar.cc/300?img=16'],
        ['name' => 'Suresh Iyer', 'position' => __('messages.committee_pos_facilities'), 'image' => 'https://i.pravatar.cc/300?img=13'],
        ['name' => 'Deepa Reddy', 'position' => __('messages.committee_pos_security'), 'image' => 'https://i.pravatar.cc/300?img=20'],
        ['name' => 'Arjun Khanna', 'position' => __('messages.committee_pos_cultural'), 'image' => 'https://i.pravatar.cc/300?img=15'],
        ['name' => 'Meena Sharma', 'position' => __('messages.committee_pos_maintenance'), 'image' => 'https://i.pravatar.cc/300?img=23'],
        ['name' => 'Rohan Gupta', 'position' => __('messages.committee_pos_member_relations'), 'image' => 'https://i.pravatar.cc/300?img=33'],
    ];
@endphp

<section
    id="management-committee"
    data-section="management-committee"
    class="scroll-mt-24 w-full border-y border-[#E6C280]/30 bg-white py-12 sm:py-16"
    x-data="{
        scroll(amount) {
            this.$refs.track.scrollBy({ left: amount, behavior: 'smooth' });
        }
    }"
>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-2xl">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#AB1E23]">
                    {{ __('messages.committee_eyebrow') }}
                </p>
                <h2 class="mt-2 text-3xl font-bold text-[#080D21] sm:text-4xl">
                    {{ __('messages.management_committee') }}
                </h2>
                <p class="mt-2 text-sm leading-relaxed text-[#0F141E] sm:text-base">
                    {{ __('messages.committee_subtitle') }}
                </p>
            </div>

            <div class="flex shrink-0 items-center gap-2">
                <button
                    type="button"
                    @click="scroll(-280)"
                    class="flex h-10 w-10 items-center justify-center rounded-full border border-[#E6EBF4] bg-white text-[#080D21] shadow-sm transition hover:border-[#E6C280] hover:bg-[#E6EBF4]"
                    aria-label="{{ __('messages.committee_prev') }}"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <button
                    type="button"
                    @click="scroll(280)"
                    class="flex h-10 w-10 items-center justify-center rounded-full border border-[#E6EBF4] bg-white text-[#080D21] shadow-sm transition hover:border-[#E6C280] hover:bg-[#E6EBF4]"
                    aria-label="{{ __('messages.committee_next') }}"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="relative mt-8">
            <div
                x-ref="track"
                class="scrollbar-thin flex snap-x snap-mandatory gap-4 overflow-x-auto scroll-smooth pb-4 pt-1"
            >
                @foreach ($members as $member)
                    <x-frontend.committee-member-card
                        :name="$member['name']"
                        :position="$member['position']"
                        :image="$member['image']"
                        :chairman="$member['chairman'] ?? false"
                    />
                @endforeach
            </div>

            <div class="pointer-events-none absolute inset-y-0 left-0 w-8 bg-gradient-to-r from-white to-transparent"></div>
            <div class="pointer-events-none absolute inset-y-0 right-0 w-8 bg-gradient-to-r from-transparent to-white"></div>
        </div>

        <p class="mt-4 text-center text-xs text-[#0F141E]/60 sm:text-sm">
            {{ __('messages.committee_swipe_hint') }}
        </p>
    </div>
</section>
