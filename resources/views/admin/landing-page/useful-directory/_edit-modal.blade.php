@props([
    'editingContact' => null,
    'activeTab' => 'services',
    'categories' => [],
    'committeeMemberOptions' => [],
    'sourceOptions' => [],
    'openOnLoad' => false,
])

@if ($editingContact)
    <div
        x-data="{
            open: @js((bool) $openOnLoad),
            title: @js(__('messages.useful_directory_edit')),
        }"
    >
        <x-common.modal maxWidth="max-w-2xl">
            <form method="POST" action="{{ route('admin.landing-page.useful-directory.update') }}" class="space-y-5">
                @csrf
                @method('PUT')
                <input type="hidden" name="_useful_directory_form" value="edit">

                @include('admin.landing-page.useful-directory._form', [
                    'contact' => $editingContact,
                    'activeTab' => $editingContact->directoryRole?->slug,
                    'activeRoleId' => $editingContact->directory_role_id,
                    'supportsCommitteeTab' => (bool) $editingContact->directoryRole?->supports_committee_link,
                    'committeeRoleId' => $committeeRoleId ?? null,
                    'committeeMemberOptions' => $committeeMemberOptions,
                    'sourceOptions' => $sourceOptions,
                    'showStatusField' => true,
                ])

                <div class="flex flex-wrap justify-end gap-3 border-t border-[#E6EBF4] pt-4">
                    <x-common.button type="button" variant="secondary" href="{{ route('admin.landing-page.useful-directory.cancel-edit') }}">
                        {{ __('messages.finance_cancel') }}
                    </x-common.button>
                    <x-common.button type="submit">
                        {{ __('messages.useful_directory_save') }}
                    </x-common.button>
                </div>
            </form>
        </x-common.modal>
    </div>
@endif
