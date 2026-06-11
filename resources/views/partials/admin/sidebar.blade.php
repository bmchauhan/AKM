<aside class="flex w-64 shrink-0 flex-col border-r border-[#E6C280]/30 bg-[#080D21]">
  <div class="flex h-16 items-center border-b border-[#E6C280]/30 px-6">
    <a href="{{ route('admin.dashboard') }}" class="flex flex-col">
      <span class="text-base font-semibold leading-relaxed text-[#E6EBF4]">{{ __('messages.brand_name') }}</span>
      <span class="text-xs leading-relaxed text-[#E6C280]">{{ __('messages.admin_panel') }}</span>
    </a>
  </div>

  <nav class="flex-1 space-y-1 p-4">
    <a
      href="{{ route('admin.dashboard') }}"
      class="flex items-center gap-3 rounded px-3 py-2 text-sm font-medium text-[#E6EBF4] transition hover:bg-[#AB1E23] hover:text-[#E6EBF4]"
    >
      <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
      </svg>
      {{ __('messages.dashboard') }}
    </a>
    <a
      href="#"
      class="flex items-center gap-3 rounded px-3 py-2 text-sm text-[#E6EBF4]/70 transition hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]"
    >
      <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
      </svg>
      {{ __('messages.members') }}
    </a>
    <a
      href="#"
      class="flex items-center gap-3 rounded px-3 py-2 text-sm text-[#E6EBF4]/70 transition hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]"
    >
      <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
      </svg>
      {{ __('messages.settings') }}
    </a>
  </nav>

  <div class="border-t border-[#E6C280]/30 p-4">
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button
        type="submit"
        class="flex w-full items-center gap-3 rounded px-3 py-2 text-sm text-[#E5989B] transition hover:bg-[#E5989B]/20"
      >
        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
        </svg>
        {{ __('messages.logout') }}
      </button>
    </form>
  </div>
</aside>
