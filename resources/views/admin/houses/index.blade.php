<x-layouts.admin :pageTitle="__('messages.houses')">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#AB1E23]">{{ __('messages.houses') }}</p>
                <h2 class="mt-1 text-xl font-bold text-[#080D21]">{{ __('messages.houses_manage') }}</h2>
                <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.houses_subtitle') }}</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.houses.index') }}" class="rounded-xl border border-[#E6EBF4] bg-white p-4 shadow-sm">
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
                    :placeholder="__('messages.houses_search_placeholder')"
                />

                <x-common.select
                    name="status"
                    :label="__('messages.houses_status')"
                    :options="[
                        ['value' => '', 'label' => __('messages.houses_filter_all_status')],
                        ['value' => 'occupied', 'label' => __('messages.houses_status_occupied')],
                        ['value' => 'vacant', 'label' => __('messages.houses_status_vacant')],
                    ]"
                    :value="$filters['status'] ?? ''"
                />

                <div class="flex items-end gap-2">
                    <x-common.button type="submit">{{ __('messages.houses_apply_filters') }}</x-common.button>
                    @if (filled($filters['house_type'] ?? null) || filled($filters['search'] ?? null) || filled($filters['status'] ?? null))
                        <a href="{{ route('admin.houses.index') }}" class="rounded px-3 py-2 text-sm text-[#0F141E]/70 hover:text-[#080D21]">{{ __('messages.houses_clear_filters') }}</a>
                    @endif
                </div>
            </div>
        </form>

        <div class="overflow-hidden rounded-xl border border-[#E6EBF4] bg-white shadow-sm">
            <div class="hidden border-b border-[#E6EBF4] bg-[#E6EBF4]/50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-[#080D21] md:grid md:grid-cols-12 md:gap-2">
                <div class="md:col-span-2">{{ __('messages.houses_unit') }}</div>
                <div class="md:col-span-4">{{ __('messages.houses_current_owner') }}</div>
                <div class="md:col-span-2">{{ __('messages.houses_status') }}</div>
                <div class="md:col-span-2">{{ __('messages.houses_since') }}</div>
                <div class="md:col-span-2 text-right">{{ __('messages.users_actions') }}</div>
            </div>

            @forelse ($houses as $house)
                @php
                    $owner = $house->currentOwnership?->mainMember;
                    $isVacant = $house->isVacant();
                @endphp
                <div class="border-b border-[#E6EBF4] px-4 py-3 last:border-b-0 md:grid md:grid-cols-12 md:items-center md:gap-2">
                    <div class="mb-1 text-sm font-semibold text-[#080D21] md:col-span-2 md:mb-0">{{ $house->label() }}</div>
                    <div class="mb-1 text-sm text-[#0F141E] md:col-span-4 md:mb-0">
                        @if ($owner)
                            {{ $owner->fullName() }}
                        @else
                            <span class="text-[#0F141E]/50">{{ __('messages.houses_no_owner') }}</span>
                        @endif
                    </div>
                    <div class="mb-1 md:col-span-2 md:mb-0">
                        @if ($isVacant)
                            <span class="inline-flex rounded-full bg-[#E5989B]/20 px-2 py-0.5 text-xs font-medium text-[#080D21]">{{ __('messages.houses_status_vacant') }}</span>
                        @else
                            <span class="inline-flex rounded-full bg-[#E6C280]/30 px-2 py-0.5 text-xs font-medium text-[#080D21]">{{ __('messages.houses_status_occupied') }}</span>
                        @endif
                    </div>
                    <div class="mb-2 text-sm text-[#0F141E]/70 md:col-span-2 md:mb-0">
                        {{ $house->currentOwnership?->started_at?->format('d M Y') ?? '—' }}
                    </div>
                    <div class="flex flex-wrap items-center justify-end gap-2 md:col-span-2">
                        <a href="{{ route('admin.houses.show', $house) }}" class="rounded px-3 py-1.5 text-xs font-medium text-[#AB1E23] transition hover:bg-[#E6EBF4]">
                            {{ __('messages.houses_view') }}
                        </a>
                        @can('houses_transfer.create')
                            <a href="{{ route('admin.houses.transfer.create', $house) }}" class="rounded bg-[#AB1E23] px-3 py-1.5 text-xs font-medium text-[#E6EBF4] transition hover:bg-[#E6C280] hover:text-[#080D21]">
                                {{ $isVacant ? __('messages.houses_assign_owner') : __('messages.houses_transfer') }}
                            </a>
                        @endcan
                    </div>
                </div>
            @empty
                <div class="px-4 py-10 text-center text-sm text-[#0F141E]/60">
                    {{ __('messages.houses_empty') }}
                </div>
            @endforelse
        </div>

        @if ($houses->hasPages())
            <div class="mt-4">{{ $houses->links() }}</div>
        @endif
    </div>
</x-layouts.admin>
