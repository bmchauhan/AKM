@props(['openOnLoad' => false])

<div x-data="{ open: @js((bool) $openOnLoad), title: @js(__('messages.directory_roles_add')) }">
    @can('landing_page_directory_roles.create')
        <x-common.button type="button" @click="open = true">
            {{ __('messages.directory_roles_add') }}
        </x-common.button>
    @endcan

    <x-common.modal maxWidth="max-w-2xl">
        <form method="POST" action="{{ route('admin.landing-page.directory-roles.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="_directory_role_form" value="add">

            @include('admin.landing-page.directory-roles._form', ['showSlugField' => true])

            <div class="flex flex-wrap justify-end gap-3 border-t border-[#E6EBF4] pt-4">
                <x-common.button type="button" variant="secondary" @click="open = false">
                    {{ __('messages.finance_cancel') }}
                </x-common.button>
                <x-common.button type="submit">
                    {{ __('messages.directory_roles_save') }}
                </x-common.button>
            </div>
        </form>
    </x-common.modal>
</div>
