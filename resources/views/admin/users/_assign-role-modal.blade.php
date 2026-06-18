@props([
    'assigningUser' => null,
    'assignableRoles' => [],
    'openOnLoad' => false,
    'currentRole' => '',
])

@if ($assigningUser && count($assignableRoles) > 0)
    <div
        x-data="{
            open: @js((bool) $openOnLoad),
            title: @js(__('messages.users_assign_role')),
            cancelUrl: @js(route('admin.users.cancel-assign-role')),
            init() {
                this.$watch('open', (value, oldValue) => {
                    if (oldValue === true && value === false) {
                        window.location.href = this.cancelUrl;
                    }
                });
            },
        }"
    >
        <x-common.modal maxWidth="max-w-lg">
            <form method="POST" action="{{ route('admin.users.assign-role') }}" class="space-y-5">
                @csrf

                <input type="hidden" name="user_id" value="{{ $assigningUser->id }}">

                <div class="rounded-xl border border-[#E6EBF4] bg-[#E6EBF4]/40 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#0F141E]/50">{{ __('messages.users_name') }}</p>
                    <p class="mt-1 text-sm font-bold text-[#080D21]">{{ $assigningUser->fullName() }}</p>
                    @if ($assigningUser->houseLabel())
                        <p class="mt-1 text-xs text-[#0F141E]/60">{{ $assigningUser->houseLabel() }}</p>
                    @endif
                    <p class="mt-2 text-xs text-[#0F141E]/60">
                        {{ __('messages.users_current_role') }}:
                        <span class="font-semibold text-[#080D21]">{{ $assigningUser->roleLabel() }}</span>
                    </p>
                </div>

                <x-common.select
                    name="assigned_role"
                    :label="__('messages.users_assign_role_label')"
                    :options="$assignableRoles"
                    :value="old('assigned_role', $currentRole)"
                    required
                />

                <p class="text-xs text-[#0F141E]/60">{{ __('messages.users_assign_role_hint') }}</p>

                <div class="flex flex-wrap justify-end gap-3 border-t border-[#E6EBF4] pt-4">
                    <x-common.button type="button" variant="secondary" :href="route('admin.users.cancel-assign-role')">
                        {{ __('messages.finance_cancel') }}
                    </x-common.button>
                    <x-common.button type="submit">
                        {{ __('messages.users_assign_role_save') }}
                    </x-common.button>
                </div>
            </form>
        </x-common.modal>
    </div>
@endif
