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
      class="flex items-center gap-3 rounded px-3 py-2 text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-[#AB1E23] text-[#E6EBF4]' : 'text-[#E6EBF4]/70 hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]' }}"
    >
      <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
      </svg>
      {{ __('messages.dashboard') }}
    </a>

    @can('users.read')
    <div x-data="{ usersOpen: @json(request()->routeIs('admin.users.*')) }">
      <button
        type="button"
        @click="usersOpen = !usersOpen"
        class="flex w-full items-center gap-3 rounded px-3 py-2 text-sm transition {{ request()->routeIs('admin.users.*') ? 'bg-[#AB1E23]/20 font-medium text-[#E6EBF4]' : 'text-[#E6EBF4]/70 hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]' }}"
        :aria-expanded="usersOpen"
      >
        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <span class="flex-1 text-left">{{ __('messages.users') }}</span>
        <svg
          class="h-4 w-4 shrink-0 transition-transform duration-200"
          :class="usersOpen ? 'rotate-90' : ''"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>

      <div
        x-show="usersOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="mt-1 space-y-1 pl-4"
      >
        <a
          href="{{ route('admin.users.index') }}"
          class="flex items-center gap-3 rounded py-2 pl-7 pr-3 text-sm transition {{ request()->routeIs('admin.users.index') || request()->routeIs('admin.users.edit') ? 'bg-[#AB1E23] font-medium text-[#E6EBF4]' : 'text-[#E6EBF4]/60 hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]' }}"
        >
          <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ request()->routeIs('admin.users.index') || request()->routeIs('admin.users.edit') ? 'bg-[#E6C280]' : 'bg-[#E6EBF4]/40' }}"></span>
          {{ __('messages.users_all') }}
        </a>
        @can('users.create')
          <a
            href="{{ route('admin.users.create') }}"
            class="flex items-center gap-3 rounded py-2 pl-7 pr-3 text-sm transition {{ request()->routeIs('admin.users.create') ? 'bg-[#AB1E23] font-medium text-[#E6EBF4]' : 'text-[#E6EBF4]/60 hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]' }}"
          >
            <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ request()->routeIs('admin.users.create') ? 'bg-[#E6C280]' : 'bg-[#E6EBF4]/40' }}"></span>
            {{ __('messages.users_add') }}
          </a>
        @endcan
      </div>
    </div>
    @endcan

    @can('members.read')
    <div x-data="{ membersOpen: @json(request()->routeIs('admin.members.*')) }">
      <button
        type="button"
        @click="membersOpen = !membersOpen"
        class="flex w-full items-center gap-3 rounded px-3 py-2 text-sm transition {{ request()->routeIs('admin.members.*') ? 'bg-[#AB1E23]/20 font-medium text-[#E6EBF4]' : 'text-[#E6EBF4]/70 hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]' }}"
        :aria-expanded="membersOpen"
      >
        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <span class="flex-1 text-left">{{ __('messages.members') }}</span>
        <svg
          class="h-4 w-4 shrink-0 transition-transform duration-200"
          :class="membersOpen ? 'rotate-90' : ''"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>

      <div
        x-show="membersOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="mt-1 space-y-1 pl-4"
      >
        <a
          href="{{ route('admin.members.index') }}"
          class="flex items-center gap-3 rounded py-2 pl-7 pr-3 text-sm transition {{ request()->routeIs('admin.members.index') || request()->routeIs('admin.members.edit') ? 'bg-[#AB1E23] font-medium text-[#E6EBF4]' : 'text-[#E6EBF4]/60 hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]' }}"
        >
          <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ request()->routeIs('admin.members.index') || request()->routeIs('admin.members.edit') ? 'bg-[#E6C280]' : 'bg-[#E6EBF4]/40' }}"></span>
          {{ __('messages.members_all') }}
        </a>
        @can('members.create')
          <a
            href="{{ route('admin.members.create') }}"
            class="flex items-center gap-3 rounded py-2 pl-7 pr-3 text-sm transition {{ request()->routeIs('admin.members.create') ? 'bg-[#AB1E23] font-medium text-[#E6EBF4]' : 'text-[#E6EBF4]/60 hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]' }}"
          >
            <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ request()->routeIs('admin.members.create') ? 'bg-[#E6C280]' : 'bg-[#E6EBF4]/40' }}"></span>
            {{ __('messages.members_add') }}
          </a>
        @endcan
      </div>
    </div>
    @endcan

    @can('settings.read')
    <div x-data="{ settingsOpen: @json(request()->routeIs('admin.settings.*')) }">
      <button
        type="button"
        @click="settingsOpen = !settingsOpen"
        class="flex w-full items-center gap-3 rounded px-3 py-2 text-sm transition {{ request()->routeIs('admin.settings.*') ? 'bg-[#AB1E23]/20 font-medium text-[#E6EBF4]' : 'text-[#E6EBF4]/70 hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]' }}"
        :aria-expanded="settingsOpen"
      >
        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <span class="flex-1 text-left">{{ __('messages.settings') }}</span>
        <svg
          class="h-4 w-4 shrink-0 transition-transform duration-200"
          :class="settingsOpen ? 'rotate-90' : ''"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>

      <div
        x-show="settingsOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="mt-1 space-y-1 pl-4"
      >
        <a
          href="{{ route('admin.settings.modules.index') }}"
          class="flex items-center gap-3 rounded py-2 pl-7 pr-3 text-sm transition {{ request()->routeIs('admin.settings.modules.*') ? 'bg-[#AB1E23] font-medium text-[#E6EBF4]' : 'text-[#E6EBF4]/60 hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]' }}"
        >
          <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ request()->routeIs('admin.settings.modules.*') ? 'bg-[#E6C280]' : 'bg-[#E6EBF4]/40' }}"></span>
          {{ __('messages.settings_modules') }}
        </a>
        <a
          href="{{ route('admin.settings.permissions.index') }}"
          class="flex items-center gap-3 rounded py-2 pl-7 pr-3 text-sm transition {{ request()->routeIs('admin.settings.permissions.*') ? 'bg-[#AB1E23] font-medium text-[#E6EBF4]' : 'text-[#E6EBF4]/60 hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]' }}"
        >
          <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ request()->routeIs('admin.settings.permissions.*') ? 'bg-[#E6C280]' : 'bg-[#E6EBF4]/40' }}"></span>
          {{ __('messages.settings_permissions') }}
        </a>
        <a
          href="{{ route('admin.settings.roles.index') }}"
          class="flex items-center gap-3 rounded py-2 pl-7 pr-3 text-sm transition {{ request()->routeIs('admin.settings.roles.*') ? 'bg-[#AB1E23] font-medium text-[#E6EBF4]' : 'text-[#E6EBF4]/60 hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]' }}"
        >
          <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ request()->routeIs('admin.settings.roles.*') ? 'bg-[#E6C280]' : 'bg-[#E6EBF4]/40' }}"></span>
          {{ __('messages.settings_roles') }}
        </a>
      </div>
    </div>
    @endcan
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
