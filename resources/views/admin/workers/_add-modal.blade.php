@props([
    'activeTab' => '',
    'openOnLoad' => false,
])

<div
    x-data="{
        open: @js((bool) $openOnLoad),
        title: @js(__('messages.workers_add')),
    }"
>
    @can('workers.create')
        <x-common.button type="button" @click="open = true">
            {{ __('messages.workers_add') }}
        </x-common.button>
    @endcan

    <x-common.modal maxWidth="max-w-2xl">
        <form method="POST" action="{{ route('admin.workers.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <input type="hidden" name="_worker_form" value="add">

            @include('admin.workers._form', [
                'activeTab' => $activeTab,
                'showSalaryFields' => true,
            ])

            <div class="flex flex-wrap justify-end gap-3 border-t border-[#E6EBF4] pt-4">
                <x-common.button type="button" variant="secondary" @click="open = false">
                    {{ __('messages.finance_cancel') }}
                </x-common.button>
                <x-common.button type="submit">
                    {{ __('messages.workers_save') }}
                </x-common.button>
            </div>
        </form>
    </x-common.modal>
</div>
