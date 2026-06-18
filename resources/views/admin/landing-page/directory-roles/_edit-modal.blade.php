@props(['editingRole' => null, 'openOnLoad' => false])

@if ($editingRole)
    <div x-data="{ open: @js((bool) $openOnLoad), title: @js(__('messages.directory_roles_edit')) }">
        <x-common.modal maxWidth="max-w-2xl">
            <form method="POST" action="{{ route('admin.landing-page.directory-roles.update') }}" class="space-y-5">
                @csrf
                @method('PUT')
                <input type="hidden" name="_directory_role_form" value="edit">

                <div class="rounded-lg border border-[#E6EBF4] bg-[#ECEAE1]/50 px-4 py-3 text-sm text-[#0F141E]/70">
                    <span class="font-semibold text-[#080D21]">{{ __('messages.directory_roles_slug') }}:</span>
                    {{ $editingRole->slug }}
                    @if ($editingRole->is_system)
                        <span class="ml-2 inline-flex rounded-full bg-[#E6C280]/30 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-[#080D21]">
                            {{ __('messages.directory_roles_system') }}
                        </span>
                    @endif
                </div>

                @include('admin.landing-page.directory-roles._form', [
                    'role' => $editingRole,
                    'showStatusField' => true,
                ])

                <div class="flex flex-wrap justify-end gap-3 border-t border-[#E6EBF4] pt-4">
                    <x-common.button type="button" variant="secondary" href="{{ route('admin.landing-page.directory-roles.cancel-edit') }}">
                        {{ __('messages.finance_cancel') }}
                    </x-common.button>
                    <x-common.button type="submit">
                        {{ __('messages.directory_roles_save') }}
                    </x-common.button>
                </div>
            </form>
        </x-common.modal>
    </div>
@endif
