<x-layouts.admin :pageTitle="__('messages.directory_roles')">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#AB1E23]">{{ __('messages.landing_page') }}</p>
                <h2 class="mt-1 text-xl font-bold text-[#080D21]">{{ __('messages.directory_roles_manage') }}</h2>
                <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.directory_roles_subtitle') }}</p>
            </div>

            @include('admin.landing-page.directory-roles._add-modal', [
                'openOnLoad' => $openAddModal ?? false,
            ])
        </div>

        <div class="overflow-hidden rounded-xl border border-[#E6EBF4] bg-white shadow-sm">
            <div class="hidden border-b border-[#E6EBF4] bg-[#E6EBF4]/50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-[#080D21] md:grid md:grid-cols-12 md:gap-2">
                <div class="md:col-span-2">{{ __('messages.directory_roles_name_en') }}</div>
                <div class="md:col-span-2">{{ __('messages.directory_roles_name_hi') }}</div>
                <div class="md:col-span-2">{{ __('messages.directory_roles_name_gu') }}</div>
                <div class="md:col-span-2">{{ __('messages.directory_roles_current_locale') }}</div>
                <div class="md:col-span-1">{{ __('messages.useful_directory_sort_order') }}</div>
                <div class="md:col-span-1">{{ __('messages.directory_roles_contacts') }}</div>
                <div class="md:col-span-2 text-right">{{ __('messages.users_actions') }}</div>
            </div>

            @forelse ($roles as $role)
                <div class="border-b border-[#E6EBF4] px-4 py-3 last:border-b-0 md:grid md:grid-cols-12 md:items-center md:gap-2">
                    <div class="mb-2 md:col-span-2 md:mb-0">
                        <p class="text-sm font-semibold text-[#080D21]">{{ $role['name_en'] }}</p>
                        <p class="text-xs text-[#0F141E]/50">{{ $role['slug'] }}</p>
                    </div>
                    <div class="mb-1 text-sm text-[#0F141E] md:col-span-2 md:mb-0">{{ $role['name_hi'] }}</div>
                    <div class="mb-1 text-sm text-[#0F141E] md:col-span-2 md:mb-0">{{ $role['name_gu'] }}</div>
                    <div class="mb-1 text-sm font-medium text-[#AB1E23] md:col-span-2 md:mb-0">{{ $role['localized_name'] }}</div>
                    <div class="mb-1 text-sm text-[#0F141E]/70 md:col-span-1 md:mb-0">{{ $role['sort_order'] }}</div>
                    <div class="mb-2 md:col-span-1 md:mb-0">
                        <span class="text-sm text-[#0F141E]">{{ $role['contacts_count'] }}</span>
                        @if ($role['supports_committee_link'])
                            <span class="mt-1 block text-[10px] font-semibold uppercase tracking-wide text-[#AB1E23]">{{ __('messages.directory_roles_committee_tab') }}</span>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center justify-end gap-1 md:col-span-2">
                        @can('landing_page_directory_roles.update')
                            <form method="POST" action="{{ route('admin.landing-page.directory-roles.open-edit') }}" class="inline-flex">
                                @csrf
                                <input type="hidden" name="role_id" value="{{ $role['id'] }}">
                                <x-common.icon-action type="submit" :title="__('messages.directory_roles_edit')">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </x-common.icon-action>
                            </form>
                        @endcan
                        @can('landing_page_directory_roles.delete')
                            @if (! $role['is_system'])
                                <x-common.delete-form
                                    :action="route('admin.landing-page.directory-roles.destroy')"
                                    :message="__('messages.directory_roles_delete_confirm')"
                                >
                                    <input type="hidden" name="role_id" value="{{ $role['id'] }}">
                                    <x-common.icon-action type="submit" :title="__('messages.finance_delete')">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </x-common.icon-action>
                                </x-common.delete-form>
                            @endif
                        @endcan
                    </div>
                </div>
            @empty
                <div class="px-4 py-10 text-center text-sm text-[#0F141E]/60">
                    {{ __('messages.directory_roles_empty') }}
                </div>
            @endforelse
        </div>

        @if ($roles->hasPages())
            <div class="rounded-xl border border-[#E6EBF4] bg-white px-4 py-3">
                {{ $roles->links() }}
            </div>
        @endif
    </div>

    @include('admin.landing-page.directory-roles._edit-modal', [
        'editingRole' => $editingRole ?? null,
        'openOnLoad' => $openEditModal ?? false,
    ])
</x-layouts.admin>
