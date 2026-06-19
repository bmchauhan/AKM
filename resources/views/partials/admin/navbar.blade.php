<header class="sticky top-0 z-20 flex h-14 shrink-0 items-center justify-between gap-2 border-b border-[#E6C280]/30 bg-[#080D21] px-3 sm:h-16 sm:gap-3 sm:px-4 lg:px-6">
  <div class="flex min-w-0 flex-1 items-center gap-2 sm:gap-3">
    <button
      type="button"
      @click="sidebarOpen = !sidebarOpen"
      class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded text-[#E6EBF4] transition hover:bg-[#E6EBF4]/10 lg:hidden"
      aria-label="{{ __('messages.toggle_sidebar') }}"
      :aria-expanded="sidebarOpen"
    >
      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
      </svg>
    </button>
    <h1 class="truncate text-sm font-medium text-[#E6EBF4] sm:text-base">
      {{ $pageTitle ?? __('messages.dashboard') }}
    </h1>
  </div>

  <div class="flex shrink-0 items-center">
    <button
      type="button"
      class="inline-flex h-8 w-8 items-center justify-center rounded text-[#E6EBF4]/85 transition hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]"
      aria-label="{{ __('messages.notifications') }}"
      title="{{ __('messages.notifications') }}"
    >
      <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>
    </button>

    <a
      href="{{ url('/') }}"
      class="inline-flex h-8 w-8 items-center justify-center rounded text-[#E6EBF4]/85 transition hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]"
      aria-label="{{ __('messages.view_site') }}"
      title="{{ __('messages.view_site') }}"
    >
      <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
      </svg>
    </a>

    <x-common.language-switcher variant="compact" />

    <span class="mx-1.5 h-5 w-px shrink-0 bg-[#E6EBF4]/20 sm:mx-2" aria-hidden="true"></span>

    <x-admin.profile-menu />
  </div>
</header>
