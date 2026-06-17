@props([
    'editingWorker' => null,
    'salaryHistory' => [],
    'openOnLoad' => false,
])

@if ($editingWorker)
    <div
        x-data="{
            open: @js((bool) $openOnLoad),
            title: @js(__('messages.workers_edit')),
        }"
    >
        <x-common.modal maxWidth="max-w-2xl">
            <form method="POST" action="{{ route('admin.workers.update') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')
                <input type="hidden" name="_worker_form" value="edit">

                @include('admin.workers._form', [
                    'worker' => $editingWorker,
                    'showStatusField' => true,
                ])

                @if (count($salaryHistory) > 0)
                    <div class="rounded-xl border border-[#E6EBF4] bg-[#E6EBF4]/30 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-[#080D21]">{{ __('messages.workers_salary_history') }}</p>
                        <ul class="mt-3 space-y-2">
                            @foreach ($salaryHistory as $rate)
                                <li class="flex flex-wrap items-center justify-between gap-2 text-sm text-[#0F141E]">
                                    <span class="font-semibold text-[#AB1E23]">{{ $rate['monthly_salary'] }}</span>
                                    <span class="text-[#0F141E]/70">{{ __('messages.workers_effective_from', ['date' => $rate['effective_from']]) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="flex flex-wrap justify-end gap-3 border-t border-[#E6EBF4] pt-4">
                    <x-common.button type="button" variant="secondary" :href="route('admin.workers.cancel-edit')">
                        {{ __('messages.finance_cancel') }}
                    </x-common.button>
                    <x-common.button type="submit">
                        {{ __('messages.workers_update') }}
                    </x-common.button>
                </div>
            </form>
        </x-common.modal>
    </div>
@endif
