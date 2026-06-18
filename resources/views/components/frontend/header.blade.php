<header class="sticky top-0 z-50 border-b border-[#E6C280]/35 bg-white/95 shadow-[0_4px_20px_rgba(8,13,33,0.06)] backdrop-blur-sm">
    <div class="h-0.5 bg-gradient-to-r from-[#080D21] via-[#AB1E23] to-[#E6C280]"></div>

    <div class="mx-auto flex max-w-7xl min-w-0 items-center gap-3 overflow-hidden px-4 py-3 sm:gap-4 sm:px-6 lg:px-8">
        <x-frontend.logo height="h-12 sm:h-14" />

        <x-frontend.navigation class="min-w-0 flex-1 justify-center" />

        <div class="flex shrink-0 items-center gap-2 sm:gap-3">
            <x-common.language-switcher />

            @auth
                <a
                    href="{{ route('admin.dashboard') }}"
                    class="inline-flex items-center gap-2 rounded-full bg-[#AB1E23] px-3 py-2 text-sm font-semibold text-[#E6EBF4] shadow-md ring-2 ring-[#AB1E23]/20 transition hover:bg-[#E6C280] hover:text-[#080D21] hover:ring-[#E6C280]/40 sm:px-4 sm:py-2.5"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span class="hidden lg:inline">{{ __('messages.admin_panel') }}</span>
                    <span class="lg:hidden">{{ __('messages.portal_short') }}</span>
                </a>
            @else
                <a
                    href="{{ route('login') }}"
                    class="inline-flex items-center gap-2 rounded-full bg-[#AB1E23] px-3 py-2 text-sm font-semibold text-[#E6EBF4] shadow-md ring-2 ring-[#AB1E23]/20 transition hover:bg-[#E6C280] hover:text-[#080D21] hover:ring-[#E6C280]/40 sm:px-4 sm:py-2.5"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="hidden lg:inline">{{ __('messages.resident_portal') }}</span>
                    <span class="lg:hidden">{{ __('messages.portal_short') }}</span>
                </a>
            @endauth
        </div>
    </div>
</header>
