<x-layouts.admin :pageTitle="__('messages.workers')">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#AB1E23]">{{ __('messages.workers') }}</p>
                <h2 class="mt-1 text-xl font-bold text-[#080D21]">{{ __('messages.workers_manage') }}</h2>
                <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.workers_subtitle') }}</p>
            </div>

            @can('workers.create')
                @include('admin.workers._add-modal', [
                    'activeTab' => $activeTab,
                    'openOnLoad' => $openAddWorkerModal ?? false,
                ])
            @endcan
        </div>

        <div class="overflow-hidden rounded-xl border border-[#E6EBF4] bg-white shadow-sm">
            <div class="flex overflow-x-auto border-b border-[#E6EBF4] bg-[#ECEAE1]/50" role="tablist" aria-label="{{ __('messages.workers_tabs') }}">
                @foreach ($workerTypes as $type)
                    <a
                        href="{{ route('admin.workers.index', ['tab' => $type['value']]) }}"
                        role="tab"
                        aria-selected="{{ $activeTab === $type['value'] ? 'true' : 'false' }}"
                        class="shrink-0 px-4 py-3.5 text-sm font-semibold transition sm:px-6 {{ $activeTab === $type['value']
                            ? 'border-b-2 border-[#AB1E23] bg-white text-[#AB1E23]'
                            : 'text-[#0F141E]/60 hover:bg-[#E6EBF4]/40 hover:text-[#080D21]' }}"
                    >
                        {{ $type['label'] }}
                    </a>
                @endforeach
            </div>

            <div class="p-4 sm:p-5">
                <div class="hidden border-b border-[#E6EBF4] bg-[#E6EBF4]/50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-[#080D21] md:grid md:grid-cols-12 md:gap-2">
                    <div class="md:col-span-3">{{ __('messages.workers_worker') }}</div>
                    <div class="md:col-span-2">{{ __('messages.workers_mobile') }}</div>
                    <div class="md:col-span-2">{{ __('messages.workers_current_salary') }}</div>
                    <div class="md:col-span-2">{{ __('messages.workers_joined_on') }}</div>
                    <div class="md:col-span-1">{{ __('messages.workers_status') }}</div>
                    <div class="md:col-span-2 text-right">{{ __('messages.users_actions') }}</div>
                </div>

                @forelse ($workers as $worker)
                    <div class="border-b border-[#E6EBF4] px-4 py-3 last:border-b-0 md:grid md:grid-cols-12 md:items-center md:gap-2">
                        <div class="mb-2 flex items-center gap-3 md:col-span-3 md:mb-0">
                            @if ($worker['profile_image_url'])
                                <img src="{{ $worker['profile_image_url'] }}" alt="" class="h-10 w-10 rounded-full object-cover ring-2 ring-[#E6EBF4]" />
                            @else
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#E6EBF4] text-sm font-semibold text-[#080D21]">
                                    {{ mb_substr($worker['name'], 0, 1) }}
                                </div>
                            @endif
                            <div>
                                <p class="text-sm font-semibold text-[#080D21]">{{ $worker['name'] }}</p>
                                @if ($worker['salary_effective_from'])
                                    <p class="text-xs text-[#0F141E]/50">{{ __('messages.workers_effective_from', ['date' => $worker['salary_effective_from']]) }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="mb-1 text-sm text-[#0F141E] md:col-span-2 md:mb-0">{{ $worker['mobile'] }}</div>
                        <div class="mb-1 text-sm font-semibold text-[#AB1E23] md:col-span-2 md:mb-0">{{ $worker['current_salary'] }}</div>
                        <div class="mb-1 text-sm text-[#0F141E]/70 md:col-span-2 md:mb-0">{{ $worker['joined_on'] }}</div>
                        <div class="mb-2 md:col-span-1 md:mb-0">
                            @if ($worker['is_active'])
                                <span class="inline-flex rounded-full bg-[#E6C280]/30 px-2 py-0.5 text-xs font-medium text-[#080D21]">{{ __('messages.workers_active') }}</span>
                            @else
                                <span class="inline-flex rounded-full bg-[#E5989B]/20 px-2 py-0.5 text-xs font-medium text-[#080D21]">{{ __('messages.workers_inactive') }}</span>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center justify-end gap-1 md:col-span-2">
                            @can('workers.update')
                                <form method="POST" action="{{ route('admin.workers.open-edit') }}" class="inline-flex">
                                    @csrf
                                    <input type="hidden" name="worker_id" value="{{ $worker['id'] }}">
                                    <x-common.icon-action type="submit" :title="__('messages.workers_edit')">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </x-common.icon-action>
                                </form>
                                <x-common.icon-action
                                    type="button"
                                    :title="__('messages.workers_salary_revision')"
                                    :href="route('admin.workers.index', ['tab' => $activeTab, 'open' => 'salary', 'salary_worker' => $worker['id']])"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </x-common.icon-action>
                            @endcan
                            @can('workers.delete')
                                <form method="POST" action="{{ route('admin.workers.destroy') }}" class="inline-flex" onsubmit="return confirm(@js(__('messages.workers_delete_confirm')))">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="worker_id" value="{{ $worker['id'] }}">
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
                        {{ __('messages.workers_empty') }}
                    </div>
                @endforelse
            </div>
        </div>

        @if ($workers->hasPages())
            <div class="rounded-xl border border-[#E6EBF4] bg-white px-4 py-3">
                {{ $workers->links() }}
            </div>
        @endif
    </div>

    @include('admin.workers._edit-modal', [
        'editingWorker' => $editingWorker ?? null,
        'salaryHistory' => $salaryHistory ?? [],
        'openOnLoad' => $openEditWorkerModal ?? false,
    ])

    @can('workers.update')
        @include('admin.workers._salary-modal', [
            'salaryWorkerId' => $salaryWorkerId ?? null,
            'openOnLoad' => $openSalaryWorkerModal ?? false,
        ])
    @endcan
</x-layouts.admin>
