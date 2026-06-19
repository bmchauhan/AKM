<x-layouts.admin :pageTitle="__('messages.houses_transfer_history')">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#AB1E23]">{{ __('messages.houses') }}</p>
                <h2 class="mt-1 text-xl font-bold text-[#080D21]">{{ __('messages.houses_transfer_history') }}</h2>
                <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.houses_transfer_history_subtitle') }}</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.houses.transfers.history') }}" class="rounded-xl border border-[#E6EBF4] bg-white p-4 shadow-sm">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
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
                    :placeholder="__('messages.houses_transfer_history_search_placeholder')"
                />

                <div class="flex items-end gap-2">
                    <x-common.button type="submit">{{ __('messages.houses_apply_filters') }}</x-common.button>
                    @if (filled($filters['house_type'] ?? null) || filled($filters['search'] ?? null))
                        <a href="{{ route('admin.houses.transfers.history') }}" class="rounded px-3 py-2 text-sm text-[#0F141E]/70 hover:text-[#080D21]">{{ __('messages.houses_clear_filters') }}</a>
                    @endif
                </div>
            </div>
        </form>

        <div class="overflow-hidden rounded-xl border border-[#E6EBF4] bg-white shadow-sm">
            <div class="hidden border-b border-[#E6EBF4] bg-[#E6EBF4]/50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-[#080D21] lg:grid lg:grid-cols-12 lg:gap-2">
                <div class="lg:col-span-2">{{ __('messages.houses_transfer_effective_date') }}</div>
                <div class="lg:col-span-1">{{ __('messages.houses_unit') }}</div>
                <div class="lg:col-span-2">{{ __('messages.houses_transfer_from') }}</div>
                <div class="lg:col-span-2">{{ __('messages.houses_transfer_to') }}</div>
                <div class="lg:col-span-2">{{ __('messages.houses_transfer_type') }}</div>
                <div class="lg:col-span-2">{{ __('messages.houses_transferred_by') }}</div>
                <div class="lg:col-span-1 text-right">{{ __('messages.users_actions') }}</div>
            </div>

            @forelse ($transfers as $entry)
                <div class="border-b border-[#E6EBF4] px-4 py-4 last:border-b-0 lg:grid lg:grid-cols-12 lg:items-start lg:gap-2">
                    <div class="mb-2 lg:col-span-2 lg:mb-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-[#0F141E]/50 lg:hidden">{{ __('messages.houses_transfer_effective_date') }}</p>
                        <p class="text-sm font-medium text-[#080D21]">{{ $entry['effective_date'] }}</p>
                    </div>
                    <div class="mb-2 lg:col-span-1 lg:mb-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-[#0F141E]/50 lg:hidden">{{ __('messages.houses_unit') }}</p>
                        <p class="text-sm font-semibold text-[#AB1E23]">{{ $entry['house_label'] }}</p>
                    </div>
                    <div class="mb-2 lg:col-span-2 lg:mb-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-[#0F141E]/50 lg:hidden">{{ __('messages.houses_transfer_from') }}</p>
                        <p class="text-sm text-[#0F141E]">{{ $entry['from_owner'] }}</p>
                    </div>
                    <div class="mb-2 lg:col-span-2 lg:mb-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-[#0F141E]/50 lg:hidden">{{ __('messages.houses_transfer_to') }}</p>
                        <p class="text-sm font-semibold text-[#080D21]">{{ $entry['to_owner'] }}</p>
                    </div>
                    <div class="mb-2 lg:col-span-2 lg:mb-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-[#0F141E]/50 lg:hidden">{{ __('messages.houses_transfer_type') }}</p>
                        <p class="text-sm text-[#0F141E]">{{ $entry['transfer_type'] }}</p>
                        @if ($entry['is_assignment'])
                            <span class="mt-1 inline-flex rounded-full bg-[#E6EBF4] px-2 py-0.5 text-xs text-[#080D21]">{{ __('messages.houses_first_assignment') }}</span>
                        @endif
                    </div>
                    <div class="mb-2 lg:col-span-2 lg:mb-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-[#0F141E]/50 lg:hidden">{{ __('messages.houses_transferred_by') }}</p>
                        <p class="text-sm text-[#0F141E]/80">{{ $entry['transferred_by'] }}</p>
                        @if ($entry['notes'])
                            <p class="mt-1 text-xs text-[#0F141E]/60">{{ $entry['notes'] }}</p>
                        @endif
                    </div>
                    <div class="flex justify-end lg:col-span-1">
                        <a href="{{ route('admin.houses.show', $entry['house_id']) }}" class="rounded px-3 py-1.5 text-xs font-medium text-[#AB1E23] transition hover:bg-[#E6EBF4]">
                            {{ __('messages.houses_view') }}
                        </a>
                    </div>
                </div>
            @empty
                <div class="px-4 py-10 text-center text-sm text-[#0F141E]/60">
                    {{ __('messages.houses_transfer_history_empty') }}
                </div>
            @endforelse
        </div>

        @if ($transfers->hasPages())
            <div>{{ $transfers->links() }}</div>
        @endif
    </div>
</x-layouts.admin>
