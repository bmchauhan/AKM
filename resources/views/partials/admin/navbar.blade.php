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

  <div class="flex items-center gap-4">
    <span class="hidden text-sm text-[#E6EBF4]/70 sm:inline">
      {{ auth()->user()->name ?? __('messages.admin_panel') }}
    </span>
    <x-common.language-switcher />
    <a
      href="{{ url('/') }}"
      class="rounded bg-[#AB1E23] px-3 py-1.5 text-sm font-medium text-[#E6EBF4] shadow transition hover:bg-[#E6C280] hover:text-[#080D21]"
    >
      {{ __('messages.view_site') }}
    </a>
  </div>
</header>
