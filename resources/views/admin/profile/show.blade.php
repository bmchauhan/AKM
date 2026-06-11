<x-layouts.admin :pageTitle="__('messages.profile_view')">
    <div class="space-y-4">
        <div class="flex items-center gap-4">
            <img
                src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=E6EBF4&color=080D21&size=128&bold=true"
                alt="{{ $user->name }}"
                class="h-20 w-20 rounded-full border-2 border-[#E6C280]/50 object-cover"
            />
            <div>
                <h2 class="text-xl font-bold text-[#080D21]">{{ $user->name }}</h2>
                <p class="text-sm text-[#0F141E]/70">{{ $user->roleLabel() }}</p>
            </div>
        </div>

        <dl class="grid gap-3 sm:grid-cols-2">
            <div class="rounded-lg border border-[#E6EBF4] bg-white p-4">
                <dt class="text-xs font-semibold uppercase tracking-wide text-[#0F141E]/60">{{ __('messages.profile_email') }}</dt>
                <dd class="mt-1 text-sm font-medium text-[#080D21]">{{ $user->email }}</dd>
            </div>
            <div class="rounded-lg border border-[#E6EBF4] bg-white p-4">
                <dt class="text-xs font-semibold uppercase tracking-wide text-[#0F141E]/60">{{ __('messages.profile_username') }}</dt>
                <dd class="mt-1 text-sm font-medium text-[#080D21]">{{ $user->username }}</dd>
            </div>
        </dl>
    </div>
</x-layouts.admin>
