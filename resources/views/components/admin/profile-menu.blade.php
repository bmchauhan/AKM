@php
    $user = auth()->user();
    $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=E6EBF4&color=080D21&size=128&bold=true';
@endphp

<div class="relative" x-data="{ open: false }">
    <button
        type="button"
        @click="open = !open"
        class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-full ring-2 ring-[#E6C280]/50 transition hover:ring-[#E6C280]"
        aria-label="{{ __('messages.profile_menu') }}"
        aria-haspopup="true"
        :aria-expanded="open"
    >
        <img
            src="{{ $avatarUrl }}"
            alt="{{ $user->name }}"
            class="h-full w-full object-cover"
        />
    </button>

    <div
        x-show="open"
        @click.away="open = false"
        x-cloak
        class="absolute right-0 z-50 mt-2 w-56 overflow-hidden rounded-lg border border-[#E6EBF4] bg-white shadow-lg"
    >
        <div class="border-b border-[#E6EBF4] bg-[#ECEAE1]/60 px-4 py-3">
            <p class="truncate text-sm font-semibold text-[#080D21]">{{ $user->name }}</p>
            <p class="truncate text-xs text-[#0F141E]/60">{{ $user->email }}</p>
        </div>

        <div class="py-1">
            <a
                href="{{ route('admin.profile') }}"
                class="flex items-center gap-2 px-4 py-2 text-sm text-[#0F141E] transition hover:bg-[#E6EBF4]"
            >
                <svg class="h-4 w-4 text-[#080D21]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                {{ __('messages.profile_view') }}
            </a>

            <a
                href="{{ route('admin.password.edit') }}"
                class="flex items-center gap-2 px-4 py-2 text-sm text-[#0F141E] transition hover:bg-[#E6EBF4]"
            >
                <svg class="h-4 w-4 text-[#080D21]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
                {{ __('messages.profile_reset_password') }}
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-[#AB1E23] transition hover:bg-[#E5989B]/20"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    {{ __('messages.logout') }}
                </button>
            </form>
        </div>
    </div>
</div>
