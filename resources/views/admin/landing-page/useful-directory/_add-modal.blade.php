@props([
    'activeTab' => 'services',
    'activeRole' => null,
    'committeeRoleId' => null,
    'committeeMemberOptions' => [],
    'sourceOptions' => [],
    'openOnLoad' => false,
])

<div
    x-data="{
        open: @js((bool) $openOnLoad),
        title: @js(__('messages.useful_directory_add')),
    }"
>
    @can('landing_page_useful_directory.create')
        <x-common.button type="button" @click="open = true">
            {{ __('messages.useful_directory_add') }}
        </x-common.button>
    @endcan

    <x-common.modal maxWidth="max-w-2xl">
        <form method="POST" action="{{ route('admin.landing-page.useful-directory.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="_useful_directory_form" value="add">

            @include('admin.landing-page.useful-directory._form', [
                'activeTab' => $activeTab,
                'activeRoleId' => $activeRole?->id,
                'supportsCommitteeTab' => (bool) ($activeRole?->supports_committee_link ?? false),
                'committeeRoleId' => $committeeRoleId,
                'committeeMemberOptions' => $committeeMemberOptions,
                'sourceOptions' => $sourceOptions,
            ])

            <div class="flex flex-wrap justify-end gap-3 border-t border-[#E6EBF4] pt-4">
                <x-common.button type="button" variant="secondary" @click="open = false">
                    {{ __('messages.finance_cancel') }}
                </x-common.button>
                <x-common.button type="submit">
                    {{ __('messages.useful_directory_save') }}
                </x-common.button>
            </div>
        </form>
    </x-common.modal>
</div>
