<x-layouts.admin :pageTitle="__('messages.visitors_view')">
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#AB1E23]">{{ __('messages.visitors') }}</p>
                <h2 class="mt-1 text-xl font-bold text-[#080D21]">{{ $entry->visitor_name }}</h2>
                <p class="mt-1 text-sm text-[#0F141E]/70">{{ $entry->entry_at->format('d M Y, h:i A') }}</p>
            </div>
            <x-common.button variant="secondary" :href="route('admin.visitors.index')">
                {{ __('messages.visitors_back') }}
            </x-common.button>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-[#E6EBF4] bg-white p-5 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-[#080D21]">{{ __('messages.visitors_details') }}</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-[#0F141E]/60">{{ __('messages.visitors_house') }}</dt>
                        <dd class="font-medium text-[#080D21]">{{ $entry->houseUnit?->label() }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-[#0F141E]/60">{{ __('messages.visitors_host') }}</dt>
                        <dd class="font-medium text-[#080D21]">{{ $entry->host?->fullName() }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-[#0F141E]/60">{{ __('messages.visitors_host_type') }}</dt>
                        <dd class="font-medium text-[#080D21]">{{ __('messages.visitors_host_' . match($entry->host_type) { 'main_member' => 'main', 'family_member' => 'family', 'rental_member' => 'rental', default => 'main' }) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-[#0F141E]/60">{{ __('messages.visitors_contact') }}</dt>
                        <dd class="font-medium text-[#080D21]">{{ $entry->visitor_contact }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-[#0F141E]/60">{{ __('messages.visitors_party_heading') }}</dt>
                        <dd class="font-medium text-[#080D21]">
                            {{ __('messages.visitors_party_summary', [
                                'total' => $entry->party_size,
                                'male' => $entry->male_count,
                                'female' => $entry->female_count,
                                'children' => $entry->children_count,
                            ]) }}
                        </dd>
                    </div>
                    @if ($entry->vehicle_number)
                    <div class="flex justify-between gap-4">
                        <dt class="text-[#0F141E]/60">{{ __('messages.visitors_vehicle') }}</dt>
                        <dd class="font-medium text-[#080D21]">{{ $entry->vehicle_number }}</dd>
                    </div>
                    @endif
                    @if ($entry->purpose)
                    <div class="flex justify-between gap-4">
                        <dt class="text-[#0F141E]/60">{{ __('messages.visitors_purpose') }}</dt>
                        <dd class="font-medium text-[#080D21]">{{ $entry->purpose }}</dd>
                    </div>
                    @endif
                    <div class="flex justify-between gap-4">
                        <dt class="text-[#0F141E]/60">{{ __('messages.workers_status') }}</dt>
                        <dd class="font-medium text-[#080D21]">{{ __('messages.visitors_status_' . $entry->status->value) }}</dd>
                    </div>
                    @if ($entry->exit_at)
                    <div class="flex justify-between gap-4">
                        <dt class="text-[#0F141E]/60">{{ __('messages.visitors_exit_time') }}</dt>
                        <dd class="font-medium text-[#080D21]">{{ $entry->exit_at->format('d M Y, h:i A') }}</dd>
                    </div>
                    @endif
                    <div class="flex justify-between gap-4">
                        <dt class="text-[#0F141E]/60">{{ __('messages.visitors_logged_by') }}</dt>
                        <dd class="font-medium text-[#080D21]">{{ $entry->loggedBy?->fullName() }}</dd>
                    </div>
                    @if ($entry->notes)
                    <div>
                        <dt class="mb-1 text-[#0F141E]/60">{{ __('messages.visitors_notes') }}</dt>
                        <dd class="text-[#080D21]">{{ $entry->notes }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            <div class="space-y-4">
                <div class="rounded-xl border border-[#E6EBF4] bg-white p-5 shadow-sm">
                    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-[#080D21]">{{ __('messages.visitors_id_proof') }}</h3>
                    @if ($entry->idProofUrl())
                        <a href="{{ $entry->idProofUrl() }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-sm font-medium text-[#AB1E23] hover:underline">
                            {{ __('messages.visitors_view_document') }}
                        </a>
                    @else
                        <p class="text-sm text-[#0F141E]/60">—</p>
                    @endif
                </div>

                <div class="rounded-xl border border-[#E6EBF4] bg-white p-5 shadow-sm">
                    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-[#080D21]">{{ __('messages.visitors_photo') }}</h3>
                    @if ($entry->photoUrl())
                        <img src="{{ $entry->photoUrl() }}" alt="{{ $entry->visitor_name }}" class="max-h-64 w-full rounded-lg object-cover" />
                    @else
                        <p class="text-sm text-[#0F141E]/60">{{ __('messages.visitors_no_photo') }}</p>
                    @endif
                </div>
            </div>
        </div>

        @can('visitors_all.delete')
        <form method="POST" action="{{ route('admin.visitors.destroy') }}" onsubmit="return confirm(@js(__('messages.visitors_delete_confirm')))">
            @csrf
            @method('DELETE')
            <input type="hidden" name="visitor_entry_id" value="{{ $entry->id }}" />
            <x-common.button type="submit" variant="secondary" class="text-[#AB1E23]">{{ __('messages.visitors_delete') }}</x-common.button>
        </form>
        @endcan
    </div>
</x-layouts.admin>
