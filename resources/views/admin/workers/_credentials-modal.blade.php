@php
    $credentials = $loginCredentials ?? null;
@endphp

@if ($credentials)
<div
    x-data="{
        open: @json($openCredentialsModal ?? false),
        username: @js($credentials['username'] ?? ''),
        password: @js($credentials['password'] ?? ''),
        workerName: @js($credentials['worker_name'] ?? ''),
        copiedField: '',
        async copy(value, field) {
            if (!value) return;
            try {
                await navigator.clipboard.writeText(value);
                this.copiedField = field;
                setTimeout(() => { if (this.copiedField === field) this.copiedField = ''; }, 2000);
            } catch (e) {}
        }
    }"
    x-init="if (open) document.body.classList.add('overflow-hidden')"
    x-effect="document.body.classList.toggle('overflow-hidden', open)"
>
    <x-common.modal :title="__('messages.workers_login_credentials_title')" max-width="max-w-lg" x-bind:open="open">
        <div class="space-y-4">
            <p class="text-sm text-[#0F141E]/80">
                {{ __('messages.workers_login_credentials_intro', ['name' => $credentials['worker_name'] ?? '']) }}
            </p>

            <div class="rounded-xl border border-[#E6EBF4] bg-[#E6EBF4]/40 p-4 space-y-4">
                <div>
                    <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-[#080D21]">{{ __('messages.user_welcome_email_username_label') }}</p>
                    <div class="flex items-center gap-2">
                        <code class="flex-1 rounded-lg bg-white px-3 py-2 text-sm font-semibold text-[#080D21]">{{ $credentials['username'] }}</code>
                        <button
                            type="button"
                            class="shrink-0 rounded-lg border border-[#E6EBF4] bg-white px-3 py-2 text-xs font-semibold text-[#AB1E23] hover:bg-[#ECEAE1]"
                            x-on:click="copy(username, 'username')"
                            x-text="copiedField === 'username' ? @js(__('messages.workers_login_copied')) : @js(__('messages.workers_login_copy'))"
                        ></button>
                    </div>
                </div>

                <div>
                    <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-[#080D21]">{{ __('messages.user_welcome_email_password_label') }}</p>
                    <div class="flex items-center gap-2">
                        <code class="flex-1 rounded-lg bg-white px-3 py-2 text-sm font-semibold text-[#AB1E23]">{{ $credentials['password'] }}</code>
                        <button
                            type="button"
                            class="shrink-0 rounded-lg border border-[#E6EBF4] bg-white px-3 py-2 text-xs font-semibold text-[#AB1E23] hover:bg-[#ECEAE1]"
                            x-on:click="copy(password, 'password')"
                            x-text="copiedField === 'password' ? @js(__('messages.workers_login_copied')) : @js(__('messages.workers_login_copy'))"
                        ></button>
                    </div>
                </div>
            </div>

            <p class="text-xs text-[#0F141E]/60">{{ __('messages.workers_login_credentials_note') }}</p>

            <div class="flex justify-end">
                <x-common.button type="button" x-on:click="open = false">{{ __('messages.workers_login_credentials_close') }}</x-common.button>
            </div>
        </div>
    </x-common.modal>
</div>
@endif
