<x-layouts.frontend :title="__('messages.our_committee').' | '.__('messages.brand_name')">
    <x-frontend.page-eyebrow-banner
        :eyebrow="__('messages.our_committee')"
        :title="__('messages.committee_page_title')"
        :subtitle="__('messages.committee_subtitle')"
    />

    <section class="bg-[#ECEAE1] py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (count($members) > 0)
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($members as $member)
                        <x-frontend.committee-member-card
                            variant="grid"
                            :name="$member['name']"
                            :position="$member['position']"
                            :image="$member['image']"
                            :has-photo="$member['has_photo']"
                            :is-chief="$member['is_chief']"
                            :is-leadership="$member['is_leadership']"
                            :bio="$member['bio']"
                        />
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-[#E6EBF4] bg-white px-6 py-16 text-center shadow-sm">
                    <p class="text-base text-[#0F141E]/70">{{ __('messages.committee_empty') }}</p>
                </div>
            @endif

            <p class="mt-10 text-center text-sm leading-relaxed text-[#0F141E]/65">
                {{ __('messages.committee_footer_note') }}
            </p>
        </div>
    </section>
</x-layouts.frontend>
