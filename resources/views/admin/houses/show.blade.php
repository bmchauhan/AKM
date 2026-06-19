<x-layouts.admin :pageTitle="$house->label()">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <a href="{{ route('admin.houses.index') }}" class="text-xs font-medium text-[#AB1E23] hover:underline">&larr; {{ __('messages.houses_back') }}</a>
                <h2 class="mt-2 text-xl font-bold text-[#080D21]">{{ $house->label() }}</h2>
                <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.houses_detail_subtitle') }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
            @can('houses_all.read')
                <a
                    href="{{ route('admin.houses.transfers.history') }}"
                    class="inline-flex items-center rounded border border-[#E6EBF4] bg-white px-4 py-2 text-sm font-medium text-[#080D21] shadow-sm transition-colors hover:bg-[#E6EBF4]"
                >
                    {{ __('messages.houses_transfer_history') }}
                </a>
            @endcan

            @can('houses_transfer.create')
                <a
                    href="{{ route('admin.houses.transfer.create', $house) }}"
                    class="inline-flex items-center rounded bg-[#AB1E23] px-4 py-2 text-sm font-medium text-[#E6EBF4] shadow transition hover:bg-[#E6C280] hover:text-[#080D21]"
                >
                    {{ $house->isVacant() ? __('messages.houses_assign_owner') : __('messages.houses_transfer') }}
                </a>
            @endcan
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-xl border border-[#E6EBF4] bg-white p-5 shadow-sm">
                <h3 class="text-sm font-bold uppercase tracking-wide text-[#080D21]">{{ __('messages.houses_current_owner') }}</h3>
                @if ($house->currentOwnership?->mainMember)
                    <p class="mt-3 text-lg font-semibold text-[#080D21]">{{ $house->currentOwnership->mainMember->fullName() }}</p>
                    <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.houses_since') }} {{ $house->currentOwnership->started_at?->format('d M Y') }}</p>
                @else
                    <p class="mt-3 text-sm text-[#0F141E]/60">{{ __('messages.houses_no_owner') }}</p>
                @endif
            </section>

            <section class="rounded-xl border border-[#E6EBF4] bg-white p-5 shadow-sm">
                <h3 class="text-sm font-bold uppercase tracking-wide text-[#080D21]">{{ __('messages.houses_finance_summary') }}</h3>
                @if ($financeByOwner->isNotEmpty())
                    <ul class="mt-3 space-y-3">
                        @foreach ($financeByOwner as $row)
                            <li class="rounded-lg bg-[#E6EBF4]/40 px-3 py-2">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="text-sm font-semibold text-[#080D21]">{{ $row['owner_name'] }}</p>
                                        <p class="text-xs text-[#0F141E]/60">{{ $row['period'] }}</p>
                                    </div>
                                    @if ($row['is_active'])
                                        <span class="shrink-0 rounded-full bg-[#E6C280]/30 px-2 py-0.5 text-xs font-medium text-[#080D21]">{{ __('messages.houses_current') }}</span>
                                    @else
                                        <span class="shrink-0 rounded-full bg-[#E5989B]/20 px-2 py-0.5 text-xs font-medium text-[#080D21]">{{ __('messages.ownership_status_former_owner') }}</span>
                                    @endif
                                </div>
                                <div class="mt-2 grid grid-cols-2 gap-2 text-xs text-[#0F141E]/80">
                                    <span>{{ __('messages.houses_collections') }}: ₹{{ $row['collections_total'] }}</span>
                                    <span>{{ __('messages.houses_maintenance_paid') }}: ₹{{ $row['maintenance_paid'] }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-3 text-sm text-[#0F141E]/60">{{ __('messages.houses_no_finance') }}</p>
                @endif
            </section>
        </div>

        <section class="overflow-hidden rounded-xl border border-[#E6EBF4] bg-white shadow-sm">
            <div class="border-b border-[#E6EBF4] bg-[#E6EBF4]/50 px-4 py-3">
                <h3 class="text-sm font-bold uppercase tracking-wide text-[#080D21]">{{ __('messages.houses_ownership_timeline') }}</h3>
            </div>

            @if ($ownershipTimeline->isNotEmpty())
                <div class="divide-y divide-[#E6EBF4]">
                    @foreach ($ownershipTimeline as $entry)
                        <div class="px-4 py-4">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div>
                                    <p class="text-sm font-semibold text-[#080D21]">{{ $entry['owner_name'] }}</p>
                                    <p class="mt-1 text-xs text-[#0F141E]/70">
                                        {{ $entry['started_at'] }} — {{ $entry['ended_at'] ?? __('messages.houses_present') }}
                                    </p>
                                    @if ($entry['transfer_type'])
                                        <p class="mt-1 text-xs text-[#0F141E]/60">{{ __('messages.houses_transfer_type') }}: {{ $entry['transfer_type'] }}</p>
                                    @endif
                                    @if ($entry['transferred_by'])
                                        <p class="mt-1 text-xs text-[#0F141E]/60">{{ __('messages.houses_transferred_by') }}: {{ $entry['transferred_by'] }}</p>
                                    @endif
                                    @if ($entry['notes'])
                                        <p class="mt-2 text-xs text-[#0F141E]/80">{{ $entry['notes'] }}</p>
                                    @endif
                                </div>
                                @if ($entry['is_active'])
                                    <span class="rounded-full bg-[#E6C280]/30 px-2 py-0.5 text-xs font-medium text-[#080D21]">{{ __('messages.houses_current') }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="px-4 py-10 text-center text-sm text-[#0F141E]/60">
                    {{ __('messages.houses_no_ownership_history') }}
                </div>
            @endif
        </section>
    </div>
</x-layouts.admin>
