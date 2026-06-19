<header class="sticky top-0 z-20 flex h-16 shrink-0 items-center justify-between border-b border-[#E6C280]/30 bg-[#080D21] px-4 sm:px-6">
  <div class="flex items-center gap-4">
    <button
      type="button"
      class="rounded p-2 text-[#E6EBF4] transition hover:bg-[#E6EBF4]/10 lg:hidden"
      aria-label="{{ __('messages.toggle_sidebar') }}"
    >
      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
      </svg>
    </button>
    <h1 class="text-base font-medium leading-relaxed text-[#E6EBF4]">
      {{ $pageTitle ?? __('messages.dashboard') }}
    </h1>
  </div>

  <div class="flex items-center gap-3 sm:gap-4">
    <x-common.language-switcher />
    <a
      href="{{ url('/') }}"
      class="rounded p-2 text-[#E6EBF4] transition hover:bg-[#E6EBF4]/10 hover:text-[#E6C280]"
      aria-label="{{ __('messages.view_site') }}"
      title="{{ __('messages.view_site') }}"
    >
      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
      </svg>
    </a>
    <x-admin.profile-menu />
  </div>
</header>
