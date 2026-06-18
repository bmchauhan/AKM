<x-layouts.frontend :title="__('messages.useful_directory').' | '.__('messages.brand_name')">
    @php
        $entries = collect($groups)->flatMap(function (array $group) {
            return collect($group['contacts'])->map(fn (array $contact) => [
                ...$contact,
                'category' => $group['slug'],
                'category_label' => $group['label'],
                'search_text' => strtolower(implode(' ', array_filter([
                    $group['label'],
                    $contact['title'],
                    $contact['name'],
                    $contact['phone'],
                    $contact['phone_secondary'],
                ]))),
            ]);
        })->values()->all();

        $categories = collect($groups)->map(fn (array $group) => [
            'slug' => $group['slug'],
            'label' => $group['label'],
            'count' => count($group['contacts']),
        ])->values()->all();
    @endphp

    <x-frontend.page-eyebrow-banner
        :eyebrow="__('messages.useful_directory')"
        :title="__('messages.useful_directory_page_title')"
        :subtitle="__('messages.useful_directory_subtitle')"
    />

    <section class="bg-[#ECEAE1] py-10 sm:py-14">
        <div
            class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"
            x-data="{
                search: '',
                activeCategory: 'all',
                entries: @js($entries),
                categories: @js($categories),
                allLabel: @js(__('messages.useful_directory_filter_all')),
                noResultsLabel: @js(__('messages.useful_directory_no_results')),
                get filteredEntries() {
                    const query = this.search.trim().toLowerCase();
                    return this.entries.filter((entry) => {
                        const matchesCategory = this.activeCategory === 'all' || entry.category === this.activeCategory;
                        const matchesSearch = query === '' || entry.search_text.includes(query);
                        return matchesCategory && matchesSearch;
                    });
                },
                setCategory(slug) {
                    this.activeCategory = slug;
                },
                clearFilters() {
                    this.search = '';
                    this.activeCategory = 'all';
                },
                chipClass(category) {
                    const map = {
                        emergency: 'bg-[#AB1E23]/10 text-[#AB1E23] border-[#AB1E23]/20',
                        government: 'bg-[#E6EBF4] text-[#080D21] border-[#080D21]/10',
                        committee: 'bg-[#E6C280]/25 text-[#080D21] border-[#E6C280]/50',
                        services: 'bg-white text-[#080D21] border-[#E6C280]/40',
                    };
                    return map[category] || 'bg-[#E6EBF4] text-[#080D21] border-[#E6EBF4]';
                },
                tel(value) {
                    return value ? String(value).replace(/\\s+/g, '') : '';
                },
            }"
        >
            @if (count($entries) > 0)
                <div class="mb-8 rounded-2xl border border-[#E6EBF4] bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="relative min-w-0 flex-1 lg:max-w-xl">
                            <label for="directory-search" class="sr-only">{{ __('messages.useful_directory_search_placeholder') }}</label>
                            <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-[#0F141E]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input
                                id="directory-search"
                                type="search"
                                x-model="search"
                                placeholder="{{ __('messages.useful_directory_search_placeholder') }}"
                                class="w-full rounded-xl border border-[#E6EBF4] bg-[#ECEAE1]/50 py-3 pl-12 pr-4 text-sm text-[#080D21] shadow-inner transition placeholder:text-[#0F141E]/45 focus:border-[#AB1E23] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20"
                            />
                        </div>

                        <p class="shrink-0 text-sm text-[#0F141E]/65">
                            <span class="font-semibold text-[#080D21]" x-text="filteredEntries.length"></span>
                            {{ __('messages.useful_directory_results_suffix') }}
                        </p>
                    </div>

                    <div class="mt-4 flex gap-2 overflow-x-auto pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                        <button
                            type="button"
                            @click="setCategory('all')"
                            :class="activeCategory === 'all'
                                ? 'border-[#AB1E23] bg-[#AB1E23] text-[#E6EBF4]'
                                : 'border-[#E6EBF4] bg-[#ECEAE1]/60 text-[#080D21] hover:border-[#E6C280] hover:bg-white'"
                            class="shrink-0 rounded-full border px-4 py-2 text-sm font-semibold transition"
                        >
                            <span x-text="allLabel"></span>
                            <span class="ml-1 opacity-80" x-text="'(' + entries.length + ')'"></span>
                        </button>

                        <template x-for="category in categories" :key="category.slug">
                            <button
                                type="button"
                                @click="setCategory(category.slug)"
                                :class="activeCategory === category.slug
                                    ? 'border-[#AB1E23] bg-[#AB1E23] text-[#E6EBF4]'
                                    : 'border-[#E6EBF4] bg-[#ECEAE1]/60 text-[#080D21] hover:border-[#E6C280] hover:bg-white'"
                                class="shrink-0 rounded-full border px-4 py-2 text-sm font-semibold transition"
                            >
                                <span x-text="category.label"></span>
                                <span class="ml-1 opacity-80" x-text="'(' + category.count + ')'"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    <template x-for="(entry, index) in filteredEntries" :key="entry.category + '-' + entry.title + '-' + entry.name + '-' + index">
                        <article class="group relative flex h-full flex-col overflow-hidden rounded-2xl border border-[#E6EBF4] bg-white p-5 shadow-sm transition duration-300 hover:border-[#E6C280]/60 hover:shadow-md">
                            <div class="pointer-events-none absolute -right-4 -top-4 h-24 w-24 rounded-full bg-[#E6EBF4]/60 transition group-hover:bg-[#E6C280]/20"></div>

                            <div class="relative flex items-start justify-between gap-3">
                                <span
                                    class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider"
                                    :class="chipClass(entry.category)"
                                    x-text="entry.category_label"
                                ></span>
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border" :class="chipClass(entry.category)">
                                    <template x-if="entry.category === 'services'">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    </template>
                                    <template x-if="entry.category === 'government'">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                    </template>
                                    <template x-if="entry.category === 'emergency'">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                    </template>
                                    <template x-if="entry.category === 'committee'">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                    </template>
                                    <template x-if="!['services','government','emergency','committee'].includes(entry.category)">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                    </template>
                                </span>
                            </div>

                            <div class="relative mt-4 flex-1">
                                <p class="text-xs font-semibold uppercase tracking-wide text-[#AB1E23]" x-text="entry.title"></p>
                                <h3 class="mt-1 text-lg font-bold leading-snug text-[#080D21]" x-text="entry.name"></h3>
                            </div>

                            <div class="relative mt-5 flex flex-wrap gap-2 border-t border-[#E6EBF4] pt-4">
                                <template x-if="entry.phone">
                                    <a
                                        :href="'tel:' + tel(entry.phone)"
                                        class="inline-flex min-w-[8rem] flex-1 items-center justify-center gap-2 rounded-lg bg-[#AB1E23] px-3 py-2.5 text-sm font-medium text-[#E6EBF4] shadow-sm transition hover:bg-[#E6C280] hover:text-[#080D21]"
                                    >
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        <span x-text="entry.phone"></span>
                                    </a>
                                </template>
                                <template x-if="entry.phone_secondary">
                                    <a
                                        :href="'tel:' + tel(entry.phone_secondary)"
                                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-[#E6C280]/50 bg-[#ECEAE1] px-3 py-2.5 text-sm font-medium text-[#080D21] transition hover:border-[#AB1E23] hover:bg-white"
                                        x-text="entry.phone_secondary"
                                    ></a>
                                </template>
                            </div>
                        </article>
                    </template>
                </div>

                <div
                    x-show="filteredEntries.length === 0"
                    x-cloak
                    class="mt-6 rounded-2xl border border-[#E6EBF4] bg-white px-6 py-16 text-center shadow-sm"
                >
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#E6EBF4] text-[#080D21]">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <p class="mt-4 text-base font-medium text-[#080D21]" x-text="noResultsLabel"></p>
                    <button
                        type="button"
                        @click="clearFilters()"
                        class="mt-4 text-sm font-semibold text-[#AB1E23] transition hover:text-[#080D21]"
                    >
                        {{ __('messages.useful_directory_clear_filters') }}
                    </button>
                </div>
            @else
                <div class="rounded-2xl border border-[#E6EBF4] bg-white px-6 py-16 text-center shadow-sm">
                    <p class="text-base text-[#0F141E]/70">{{ __('messages.useful_directory_empty') }}</p>
                </div>
            @endif
        </div>
    </section>
</x-layouts.frontend>
