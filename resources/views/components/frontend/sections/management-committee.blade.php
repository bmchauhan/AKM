@php
    $members = app(\App\Services\Frontend\FrontendCommitteeService::class)->publicMembers();
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
            @if (count($members) > 0)
            <div
                x-ref="track"
                class="scrollbar-thin flex snap-x snap-mandatory gap-4 overflow-x-auto scroll-smooth pb-4 pt-1"
            >
                @foreach ($members as $member)
                    <x-frontend.committee-member-card
                        :name="$member['name']"
                        :position="$member['position']"
                        :image="$member['image']"
                        :has-photo="$member['has_photo']"
                        :is-chief="$member['is_chief']"
                        :is-leadership="$member['is_leadership']"
                    />
                @endforeach
            </div>

            <div class="pointer-events-none absolute inset-y-0 left-0 w-8 bg-gradient-to-r from-white to-transparent"></div>
            <div class="pointer-events-none absolute inset-y-0 right-0 w-8 bg-gradient-to-r from-transparent to-white"></div>
            @else
                <p class="rounded-2xl border border-[#E6EBF4] bg-[#ECEAE1]/40 px-6 py-12 text-center text-sm text-[#0F141E]/70">
                    {{ __('messages.committee_empty') }}
                </p>
            @endif
        </div>

        @if (count($members) > 0)
        <p class="mt-4 text-center text-xs text-[#0F141E]/60 sm:text-sm">
            {{ __('messages.committee_swipe_hint') }}
        </p>
        @endif
    </div>
</section>
