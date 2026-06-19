<header
    class="sticky top-0 z-[100] isolate border-b border-[#E6C280]/35 bg-white/95 shadow-[0_4px_20px_rgba(8,13,33,0.06)] backdrop-blur-sm"
    x-data="{ mobileMenuOpen: false }"
    @keydown.escape.window="mobileMenuOpen = false"
>
    <div class="h-0.5 bg-gradient-to-r from-[#080D21] via-[#AB1E23] to-[#E6C280]"></div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex min-w-0 items-center gap-2 py-3 sm:gap-3">
            <button
                type="button"
                @click="mobileMenuOpen = !mobileMenuOpen"
                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[#080D21] transition hover:bg-[#E6EBF4] lg:hidden"
                :aria-expanded="mobileMenuOpen"
                aria-controls="frontend-mobile-nav"
                aria-label="{{ __('messages.toggle_menu') }}"
            >
                <svg x-show="!mobileMenuOpen" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg x-show="mobileMenuOpen" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <x-frontend.logo height="h-10 sm:h-12 lg:h-14" class="min-w-0 flex-1 lg:flex-none" />

            <x-frontend.navigation class="hidden min-w-0 flex-1 justify-center lg:flex" />

            <div class="ml-auto flex shrink-0 items-center gap-2 sm:gap-3">
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

        <nav
            id="frontend-mobile-nav"
            x-show="mobileMenuOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
            class="border-t border-[#E6EBF4] pb-4 pt-3 lg:hidden"
            @click="if ($event.target.closest('a')) mobileMenuOpen = false"
        >
            <div class="flex flex-col gap-1">
                @php
                    $mobileNavItems = [
                        ['label' => 'home', 'route' => 'home'],
                        ['label' => 'news', 'route' => 'news'],
                        ['label' => 'our_committee', 'route' => 'committee'],
                        ['label' => 'useful_directory', 'route' => 'useful-directory'],
                    ];
                @endphp

                @foreach ($mobileNavItems as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        @class([
                            'rounded-lg px-3 py-2.5 text-sm font-semibold transition',
                            'bg-[#AB1E23] text-[#E6EBF4]' => request()->routeIs($item['route']),
                            'text-[#080D21] hover:bg-[#E6EBF4]' => ! request()->routeIs($item['route']),
                        ])
                    >
                        {{ __('messages.'.$item['label']) }}
                    </a>
                @endforeach
            </div>
        </nav>
    </div>
</header>
