@php
    $updates = [
        ['type' => 'notice', 'date' => __('messages.updates_item_1_date'), 'title' => __('messages.updates_item_1_title'), 'excerpt' => __('messages.updates_item_1_excerpt'), 'featured' => true],
        ['type' => 'event', 'date' => __('messages.updates_item_2_date'), 'title' => __('messages.updates_item_2_title'), 'excerpt' => __('messages.updates_item_2_excerpt')],
        ['type' => 'news', 'date' => __('messages.updates_item_3_date'), 'title' => __('messages.updates_item_3_title'), 'excerpt' => __('messages.updates_item_3_excerpt')],
        ['type' => 'notice', 'date' => __('messages.updates_item_4_date'), 'title' => __('messages.updates_item_4_title'), 'excerpt' => __('messages.updates_item_4_excerpt')],
        ['type' => 'event', 'date' => __('messages.updates_item_5_date'), 'title' => __('messages.updates_item_5_title'), 'excerpt' => __('messages.updates_item_5_excerpt')],
        ['type' => 'news', 'date' => __('messages.updates_item_6_date'), 'title' => __('messages.updates_item_6_title'), 'excerpt' => __('messages.updates_item_6_excerpt')],
    ];

    $filters = ['all', 'news', 'event', 'notice'];
@endphp

<section
    id="latest-updates"
    data-section="latest-updates"
    class="scroll-mt-24 w-full bg-[#ECEAE1] py-16 sm:py-20"
>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#AB1E23]">
                {{ __('messages.updates_eyebrow') }}
            </p>
            <h2 class="mt-2 text-3xl font-bold text-[#080D21] sm:text-4xl">
                {{ __('messages.latest_updates') }}
            </h2>
            <p class="mt-3 text-base leading-relaxed text-[#0F141E]">
                {{ __('messages.updates_subtitle') }}
            </p>
        </div>

        <div class="mt-10" x-data="{ filter: 'all' }">
            <div class="flex flex-wrap items-center gap-2">
                @foreach ($filters as $filter)
                    <button
                        type="button"
                        @click="filter = '{{ $filter }}'"
                        :class="filter === '{{ $filter }}'
                            ? 'bg-[#080D21] text-[#E6EBF4] ring-[#080D21]'
                            : 'bg-white text-[#080D21] ring-[#E6EBF4] hover:ring-[#E6C280]/50'"
                        class="rounded-full px-4 py-1.5 text-sm font-semibold ring-1 transition"
                    >
                        {{ __('messages.updates_filter_' . $filter) }}
                    </button>
                @endforeach
            </div>

            @foreach ($updates as $update)
                @if ($update['featured'] ?? false)
                    <div
                        x-show="filter === 'all' || filter === '{{ $update['type'] }}'"
                        x-cloak
                        class="mt-8 rounded-xl border border-[#AB1E23]/20 bg-white p-6 shadow-sm ring-1 ring-[#AB1E23]/10 sm:p-8"
                    >
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="inline-flex rounded-full bg-[#AB1E23] px-2.5 py-1 text-xs font-semibold text-[#E6EBF4]">
                                        {{ __('messages.updates_featured') }}
                                    </span>
                                    <span class="inline-flex rounded-full bg-[#E5989B]/25 px-2.5 py-1 text-xs font-semibold text-[#AB1E23] ring-1 ring-[#AB1E23]/20 ring-inset">
                                        {{ __('messages.updates_type_notice') }}
                                    </span>
                                    <time class="text-xs font-medium text-[#0F141E]/60">{{ $update['date'] }}</time>
                                </div>
                                <h3 class="mt-4 text-xl font-bold text-[#080D21] sm:text-2xl">
                                    {{ $update['title'] }}
                                </h3>
                                <p class="mt-3 max-w-3xl text-sm leading-relaxed text-[#0F141E] sm:text-base">
                                    {{ $update['excerpt'] }}
                                </p>
                            </div>
                            <a
                                href="#"
                                class="inline-flex shrink-0 items-center justify-center rounded-full bg-[#AB1E23] px-5 py-2.5 text-sm font-semibold text-[#E6EBF4] shadow transition hover:bg-[#E6C280] hover:text-[#080D21]"
                            >
                                {{ __('messages.read_full_notice') }}
                            </a>
                        </div>
                    </div>
                @endif
            @endforeach

            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($updates as $update)
                    @continue($update['featured'] ?? false)

                    <div x-show="filter === 'all' || filter === '{{ $update['type'] }}'" x-cloak>
                        <x-frontend.update-card
                            :type="$update['type']"
                            :date="$update['date']"
                            :title="$update['title']"
                            :excerpt="$update['excerpt']"
                            class="h-full"
                        />
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
