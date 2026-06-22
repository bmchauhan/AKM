<x-layouts.admin :pageTitle="__('messages.visitors_today')">
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#AB1E23]">{{ __('messages.visitors') }}</p>
                <h2 class="mt-1 text-xl font-bold text-[#080D21]">{{ __('messages.visitors_today') }}</h2>
                <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.visitors_today_subtitle') }}</p>
            </div>
            @can('visitors_log.create')
            <x-common.button :href="route('admin.visitors.log')">{{ __('messages.visitors_log_new') }}</x-common.button>
            @endcan
        </div>

        <div class="overflow-hidden rounded-xl border border-[#E6EBF4] bg-white shadow-sm">
            @forelse ($entries as $entry)
                <div class="border-b border-[#E6EBF4] px-4 py-4 last:border-b-0 sm:grid sm:grid-cols-12 sm:items-center sm:gap-3">
                    <div class="sm:col-span-3">
                        <p class="font-semibold text-[#080D21]">{{ $entry->visitor_name }}</p>
                        <p class="text-sm text-[#0F141E]/70">{{ $entry->visitor_contact }}</p>
                    </div>
                    <div class="mt-2 sm:col-span-3 sm:mt-0">
                        <p class="text-sm font-medium text-[#080D21]">{{ $entry->houseUnit?->label() }}</p>
                        <p class="text-sm text-[#0F141E]/70">{{ $entry->host?->fullName() }}</p>
                    </div>
                    <div class="mt-2 sm:col-span-2 sm:mt-0">
                        <p class="text-sm text-[#0F141E]">
                            {{ __('messages.visitors_party_summary', [
                                'total' => $entry->party_size,
                                'male' => $entry->male_count,
                                'female' => $entry->female_count,
                                'children' => $entry->children_count,
                            ]) }}
                        </p>
                        <p class="text-xs text-[#0F141E]/60">{{ $entry->entry_at->format('h:i A') }}</p>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-2 sm:col-span-4 sm:mt-0 sm:justify-end">
                        @can('visitors_all.read')
                        <a href="{{ route('admin.visitors.show', $entry) }}" class="rounded px-3 py-1.5 text-sm text-[#080D21] hover:bg-[#E6EBF4]">
                            {{ __('messages.visitors_view') }}
                        </a>
                        @endcan
                        @can('visitors_log.update')
                        <form method="POST" action="{{ route('admin.visitors.checkout') }}">
                            @csrf
                            <input type="hidden" name="visitor_entry_id" value="{{ $entry->id }}" />
                            <x-common.button type="submit" class="min-h-[40px]">{{ __('messages.visitors_checkout') }}</x-common.button>
                        </form>
                        @endcan
                    </div>
                </div>
            @empty
                <div class="px-4 py-12 text-center text-sm text-[#0F141E]/60">
                    {{ __('messages.visitors_today_empty') }}
                </div>
            @endforelse
        </div>

        @if ($entries->hasPages())
            <div class="mt-4">{{ $entries->links() }}</div>
        @endif
    </div>
</x-layouts.admin>
