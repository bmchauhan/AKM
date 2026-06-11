<x-layouts.admin :pageTitle="__('messages.profile_reset_password')">
    <form method="POST" action="{{ route('admin.password.update') }}" class="max-w-md space-y-4" novalidate>
        @csrf
        @method('PUT')

        <x-common.password
            name="current_password"
            :label="__('messages.password_current')"
        />

        <x-common.password
            name="password"
            :label="__('messages.password_new')"
        />

        <x-common.password
            name="password_confirmation"
            :label="__('messages.password_confirm')"
        />

        <x-common.button type="submit">
            {{ __('messages.password_update_submit') }}
        </x-common.button>
    </form>
</x-layouts.admin>
