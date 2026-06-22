<x-layouts.admin :pageTitle="__('messages.visitors_log')">
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#AB1E23]">{{ __('messages.visitors') }}</p>
                <h2 class="mt-1 text-xl font-bold text-[#080D21]">{{ __('messages.visitors_log') }}</h2>
                <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.visitors_log_subtitle') }}</p>
            </div>
            <x-common.button variant="secondary" :href="route('admin.visitors.today')">
                {{ __('messages.visitors_today') }}
            </x-common.button>
        </div>

        <form
            method="POST"
            action="{{ route('admin.visitors.store') }}"
            enctype="multipart/form-data"
            class="rounded-xl border border-[#E6EBF4] bg-white p-4 sm:p-6 shadow-sm"
            autocomplete="off"
            novalidate
        >
            @csrf
            @include('admin.visitors._form', ['houses' => $houses])

            <div class="mt-8 flex flex-wrap gap-3 border-t border-[#E6EBF4] pt-6">
                <x-common.button type="submit" class="min-h-[48px] min-w-[140px] text-base">
                    {{ __('messages.visitors_save') }}
                </x-common.button>
                <x-common.button type="button" variant="secondary" :href="route('admin.visitors.today')">
                    {{ __('messages.users_cancel') }}
                </x-common.button>
            </div>
        </form>
    </div>
</x-layouts.admin>
