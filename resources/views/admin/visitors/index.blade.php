<x-layouts.admin :pageTitle="__('messages.visitors_all')">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#AB1E23]">{{ __('messages.visitors') }}</p>
                <h2 class="mt-1 text-xl font-bold text-[#080D21]">{{ __('messages.visitors_all') }}</h2>
                <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.visitors_all_subtitle') }}</p>
            </div>
            @can('visitors_log.create')
            <x-common.button :href="route('admin.visitors.log')">{{ __('messages.visitors_log_new') }}</x-common.button>
            @endcan
        </div>

        <form method="GET" action="{{ route('admin.visitors.index') }}" class="rounded-xl border border-[#E6EBF4] bg-white p-4 shadow-sm">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <x-common.select
                    name="house_type"
                    :label="__('messages.users_house_type')"
                    :options="collect($houseTypes)->map(fn ($t) => ['value' => $t->value, 'label' => $t->label()])->all()"
                    :value="$filters['house_type'] ?? ''"
                >
                    <option value="">{{ __('messages.houses_filter_all_types') }}</option>
                </x-common.select>

                <x-common.input
                    name="search"
                    :label="__('messages.houses_search')"
                    :value="$filters['search'] ?? ''"
                    :placeholder="__('messages.visitors_search_placeholder')"
                />

                <x-common.select
                    name="host_type"
                    :label="__('messages.visitors_host_type')"
                    :options="[
                        ['value' => 'main_member', 'label' => __('messages.visitors_host_main')],
                        ['value' => 'family_member', 'label' => __('messages.visitors_host_family')],
                        ['value' => 'rental_member', 'label' => __('messages.visitors_host_rental')],
                    ]"
                    :value="$filters['host_type'] ?? ''"
                >
                    <option value="">{{ __('messages.visitors_filter_all_hosts') }}</option>
                </x-common.select>

                <x-common.select
                    name="status"
                    :label="__('messages.workers_status')"
                    :options="[
                        ['value' => 'active', 'label' => __('messages.visitors_status_active')],
                        ['value' => 'exited', 'label' => __('messages.visitors_status_exited')],
                    ]"
                    :value="$filters['status'] ?? ''"
                >
                    <option value="">{{ __('messages.visitors_filter_all_status') }}</option>
                </x-common.select>

                <x-common.input name="date_from" type="date" :label="__('messages.visitors_date_from')" :value="$filters['date_from'] ?? ''" />
                <x-common.input name="date_to" type="date" :label="__('messages.visitors_date_to')" :value="$filters['date_to'] ?? ''" />

                <div class="flex flex-col justify-end gap-2 sm:col-span-2">
                    <label class="flex items-center gap-2 text-sm text-[#0F141E]">
                        <input type="checkbox" name="rental_only" value="1" @checked($filters['rental_only'] ?? false) class="rounded border-[#E6EBF4] text-[#AB1E23] focus:ring-[#AB1E23]/20" />
                        {{ __('messages.visitors_filter_rental_only') }}
                    </label>
                    <label class="flex items-center gap-2 text-sm text-[#0F141E]">
                        <input type="checkbox" name="females_only" value="1" @checked($filters['females_only'] ?? false) class="rounded border-[#E6EBF4] text-[#AB1E23] focus:ring-[#AB1E23]/20" />
                        {{ __('messages.visitors_filter_females_only') }}
                    </label>
                </div>

                <div class="flex items-end gap-2 sm:col-span-2">
                    <x-common.button type="submit">{{ __('messages.houses_apply_filters') }}</x-common.button>
                    <a href="{{ route('admin.visitors.index') }}" class="rounded px-3 py-2 text-sm text-[#0F141E]/70 hover:text-[#080D21]">{{ __('messages.houses_clear_filters') }}</a>
                </div>
            </div>
        </form>

        <div class="overflow-hidden rounded-xl border border-[#E6EBF4] bg-white shadow-sm">
            <div class="hidden border-b border-[#E6EBF4] bg-[#E6EBF4]/50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-[#080D21] lg:grid lg:grid-cols-12 lg:gap-2">
                <div class="lg:col-span-2">{{ __('messages.visitors_entry_time') }}</div>
                <div class="lg:col-span-2">{{ __('messages.visitors_name') }}</div>
                <div class="lg:col-span-2">{{ __('messages.visitors_house') }}</div>
                <div class="lg:col-span-2">{{ __('messages.visitors_host') }}</div>
                <div class="lg:col-span-2">{{ __('messages.visitors_party_heading') }}</div>
                <div class="lg:col-span-2 text-right">{{ __('messages.users_actions') }}</div>
            </div>

            @forelse ($entries as $entry)
                <div class="border-b border-[#E6EBF4] px-4 py-4 last:border-b-0 lg:grid lg:grid-cols-12 lg:items-center lg:gap-2">
                    <div class="lg:col-span-2">
                        <p class="text-sm font-medium text-[#080D21]">{{ $entry->entry_at->format('d M Y') }}</p>
                        <p class="text-xs text-[#0F141E]/60">{{ $entry->entry_at->format('h:i A') }}</p>
                        @if ($entry->isRentalVisit())
                            <span class="mt-1 inline-block rounded bg-[#E6C280]/40 px-2 py-0.5 text-xs font-medium text-[#080D21]">{{ __('messages.visitors_host_rental') }}</span>
                        @endif
                    </div>
                    <div class="mt-2 lg:col-span-2 lg:mt-0">
                        <p class="font-medium text-[#080D21]">{{ $entry->visitor_name }}</p>
                        <p class="text-xs text-[#0F141E]/60">{{ $entry->visitor_contact }}</p>
                    </div>
                    <div class="mt-2 text-sm lg:col-span-2 lg:mt-0">{{ $entry->houseUnit?->label() }}</div>
                    <div class="mt-2 text-sm lg:col-span-2 lg:mt-0">{{ $entry->host?->fullName() }}</div>
                    <div class="mt-2 text-sm lg:col-span-2 lg:mt-0">
                        {{ __('messages.visitors_party_summary', [
                            'total' => $entry->party_size,
                            'male' => $entry->male_count,
                            'female' => $entry->female_count,
                            'children' => $entry->children_count,
                        ]) }}
                        @if ($entry->female_count > 0)
                            <span class="text-[#AB1E23]">· {{ $entry->female_count }}F</span>
                        @endif
                    </div>
                    <div class="mt-3 flex justify-end gap-2 lg:col-span-2 lg:mt-0">
                        <a href="{{ route('admin.visitors.show', $entry) }}" class="rounded px-3 py-1.5 text-sm font-medium text-[#AB1E23] hover:bg-[#E6EBF4]">
                            {{ __('messages.visitors_view') }}
                        </a>
                    </div>
                </div>
            @empty
                <div class="px-4 py-12 text-center text-sm text-[#0F141E]/60">{{ __('messages.visitors_empty') }}</div>
            @endforelse
        </div>

        @if ($entries->hasPages())
            <div>{{ $entries->links() }}</div>
        @endif
    </div>
</x-layouts.admin>
