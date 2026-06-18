<x-layouts.admin :pageTitle="__('messages.useful_directory')">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#AB1E23]">{{ __('messages.landing_page') }}</p>
                <h2 class="mt-1 text-xl font-bold text-[#080D21]">{{ __('messages.useful_directory_manage') }}</h2>
                <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.useful_directory_admin_subtitle') }}</p>
            </div>

            @include('admin.landing-page.useful-directory._add-modal', [
                'activeTab' => $activeTab,
                'activeRole' => $activeRole ?? null,
                'committeeRoleId' => $committeeRoleId ?? null,
                'committeeMemberOptions' => $committeeMemberOptions,
                'sourceOptions' => $sourceOptions,
                'openOnLoad' => $openAddModal ?? false,
            ])
        </div>

        <div class="overflow-hidden rounded-xl border border-[#E6EBF4] bg-white shadow-sm">
            <div class="flex overflow-x-auto border-b border-[#E6EBF4] bg-[#ECEAE1]/50" role="tablist" aria-label="{{ __('messages.useful_directory_tabs') }}">
                @foreach ($categories as $category)
                    <a
                        href="{{ route('admin.landing-page.useful-directory.index', ['tab' => $category['slug']]) }}"
                        role="tab"
                        aria-selected="{{ $activeTab === $category['slug'] ? 'true' : 'false' }}"
                        class="shrink-0 px-4 py-3.5 text-sm font-semibold transition sm:px-6 {{ $activeTab === $category['slug']
                            ? 'border-b-2 border-[#AB1E23] bg-white text-[#AB1E23]'
                            : 'text-[#0F141E]/60 hover:bg-[#E6EBF4]/40 hover:text-[#080D21]' }}"
                    >
                        {{ $category['label'] }}
                    </a>
                @endforeach
            </div>

            <div class="p-4 sm:p-5">
                <div class="hidden border-b border-[#E6EBF4] bg-[#E6EBF4]/50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-[#080D21] md:grid md:grid-cols-12 md:gap-2">
                    <div class="md:col-span-3">{{ __('messages.useful_directory_service_title') }}</div>
                    <div class="md:col-span-2">{{ __('messages.useful_directory_contact_name') }}</div>
                    <div class="md:col-span-2">{{ __('messages.useful_directory_phone_primary') }}</div>
                    <div class="md:col-span-1">{{ __('messages.useful_directory_entry_type') }}</div>
                    <div class="md:col-span-1">{{ __('messages.useful_directory_sort_order') }}</div>
                    <div class="md:col-span-1">{{ __('messages.useful_directory_active') }}</div>
                    <div class="md:col-span-2 text-right">{{ __('messages.users_actions') }}</div>
                </div>

                @forelse ($contacts as $contact)
                    <div class="border-b border-[#E6EBF4] px-4 py-3 last:border-b-0 md:grid md:grid-cols-12 md:items-center md:gap-2">
                        <div class="mb-2 md:col-span-3 md:mb-0">
                            <p class="text-sm font-semibold text-[#080D21]">{{ $contact['title'] }}</p>
                            @if ($contact['notes'])
                                <p class="mt-0.5 text-xs text-[#0F141E]/50">{{ \Illuminate\Support\Str::limit($contact['notes'], 60) }}</p>
                            @endif
                        </div>
                        <div class="mb-1 text-sm text-[#0F141E] md:col-span-2 md:mb-0">{{ $contact['contact_name'] }}</div>
                        <div class="mb-1 text-sm font-medium text-[#AB1E23] md:col-span-2 md:mb-0">{{ $contact['phone_primary'] }}</div>
                        <div class="mb-1 text-xs text-[#0F141E]/70 md:col-span-1 md:mb-0">{{ $contact['source_label'] }}</div>
                        <div class="mb-1 text-sm text-[#0F141E]/70 md:col-span-1 md:mb-0">{{ $contact['sort_order'] }}</div>
                        <div class="mb-2 md:col-span-1 md:mb-0">
                            @if ($contact['is_active'])
                                <span class="inline-flex rounded-full bg-[#E6C280]/30 px-2 py-0.5 text-xs font-medium text-[#080D21]">{{ __('messages.useful_directory_active') }}</span>
                            @else
                                <span class="inline-flex rounded-full bg-[#E5989B]/20 px-2 py-0.5 text-xs font-medium text-[#080D21]">{{ __('messages.useful_directory_inactive') }}</span>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center justify-end gap-1 md:col-span-2">
                            @can('landing_page_useful_directory.update')
                                <form method="POST" action="{{ route('admin.landing-page.useful-directory.open-edit') }}" class="inline-flex">
                                    @csrf
                                    <input type="hidden" name="contact_id" value="{{ $contact['id'] }}">
                                    <x-common.icon-action type="submit" :title="__('messages.useful_directory_edit')">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </x-common.icon-action>
                                </form>
                            @endcan
                            @can('landing_page_useful_directory.delete')
                                <form method="POST" action="{{ route('admin.landing-page.useful-directory.destroy') }}" class="inline-flex" onsubmit="return confirm(@js(__('messages.useful_directory_delete_confirm')))">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="contact_id" value="{{ $contact['id'] }}">
                                    <x-common.icon-action type="submit" :title="__('messages.finance_delete')">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </x-common.icon-action>
                                </form>
                            @endcan
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-10 text-center text-sm text-[#0F141E]/60">
                        {{ __('messages.useful_directory_empty') }}
                    </div>
                @endforelse
            </div>
        </div>

        @if ($contacts->hasPages())
            <div class="rounded-xl border border-[#E6EBF4] bg-white px-4 py-3">
                {{ $contacts->links() }}
            </div>
        @endif
    </div>

    @include('admin.landing-page.useful-directory._edit-modal', [
        'editingContact' => $editingContact ?? null,
        'committeeRoleId' => $committeeRoleId ?? null,
        'committeeMemberOptions' => $editingCommitteeOptions ?? [],
        'sourceOptions' => $sourceOptions,
        'openOnLoad' => $openEditModal ?? false,
    ])
</x-layouts.admin>
