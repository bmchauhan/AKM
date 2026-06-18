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
        @can('users_all.read')
        <a
          href="{{ route('admin.users.index') }}"
          class="flex items-center gap-3 rounded py-2 pl-7 pr-3 text-sm transition {{ request()->routeIs('admin.users.index') || request()->routeIs('admin.users.edit') ? 'bg-[#AB1E23] font-medium text-[#E6EBF4]' : 'text-[#E6EBF4]/60 hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]' }}"
        >
          <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ request()->routeIs('admin.users.index') || request()->routeIs('admin.users.edit') ? 'bg-[#E6C280]' : 'bg-[#E6EBF4]/40' }}"></span>
          {{ __('messages.users_all') }}
        </a>
        @endcan
        @can('users_add.create')
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
        @can('members_all.read')
        <a
          href="{{ route('admin.members.index') }}"
          class="flex items-center gap-3 rounded py-2 pl-7 pr-3 text-sm transition {{ request()->routeIs('admin.members.index') || request()->routeIs('admin.members.edit') ? 'bg-[#AB1E23] font-medium text-[#E6EBF4]' : 'text-[#E6EBF4]/60 hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]' }}"
        >
          <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ request()->routeIs('admin.members.index') || request()->routeIs('admin.members.edit') ? 'bg-[#E6C280]' : 'bg-[#E6EBF4]/40' }}"></span>
          {{ __('messages.members_all') }}
        </a>
        @endcan
        @can('members_add.create')
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

    @can('finance.read')
    <div x-data="{ financeOpen: @json(request()->routeIs('admin.finance.*')) }">
      <button
        type="button"
        @click="financeOpen = !financeOpen"
        class="flex w-full items-center gap-3 rounded px-3 py-2 text-sm transition {{ request()->routeIs('admin.finance.*') ? 'bg-[#AB1E23]/20 font-medium text-[#E6EBF4]' : 'text-[#E6EBF4]/70 hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]' }}"
        :aria-expanded="financeOpen"
      >
        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="flex-1 text-left">{{ __('messages.finance') }}</span>
        <svg
          class="h-4 w-4 shrink-0 transition-transform duration-200"
          :class="financeOpen ? 'rotate-90' : ''"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>

      <div
        x-show="financeOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="mt-1 space-y-1 pl-4"
      >
        @can('finance_overview.read')
        <a
          href="{{ route('admin.finance.index') }}"
          class="flex items-center gap-3 rounded py-2 pl-7 pr-3 text-sm transition {{ request()->routeIs('admin.finance.index') ? 'bg-[#AB1E23] font-medium text-[#E6EBF4]' : 'text-[#E6EBF4]/60 hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]' }}"
        >
          <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ request()->routeIs('admin.finance.index') ? 'bg-[#E6C280]' : 'bg-[#E6EBF4]/40' }}"></span>
          {{ __('messages.finance_overview') }}
        </a>
        @endcan
        @can('finance_house_ledger.read')
        <a
          href="{{ route('admin.finance.maintenance-ledger.house') }}"
          class="flex items-center gap-3 rounded py-2 pl-7 pr-3 text-sm transition {{ request()->routeIs('admin.finance.maintenance-ledger.house') ? 'bg-[#AB1E23] font-medium text-[#E6EBF4]' : 'text-[#E6EBF4]/60 hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]' }}"
        >
          <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ request()->routeIs('admin.finance.maintenance-ledger.house') ? 'bg-[#E6C280]' : 'bg-[#E6EBF4]/40' }}"></span>
          {{ __('messages.finance_house_ledger') }}
        </a>
        @endcan
        @can('finance_maintenance_ledger.read')
        <a
          href="{{ route('admin.finance.maintenance-ledger.index') }}"
          class="flex items-center gap-3 rounded py-2 pl-7 pr-3 text-sm transition {{ request()->routeIs('admin.finance.maintenance-ledger.index') ? 'bg-[#AB1E23] font-medium text-[#E6EBF4]' : 'text-[#E6EBF4]/60 hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]' }}"
        >
          <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ request()->routeIs('admin.finance.maintenance-ledger.index') ? 'bg-[#E6C280]' : 'bg-[#E6EBF4]/40' }}"></span>
          {{ __('messages.finance_maintenance_ledger') }}
        </a>
        @endcan
        @can('finance_collections.read')
        <a
          href="{{ route('admin.finance.collections.index') }}"
          class="flex items-center gap-3 rounded py-2 pl-7 pr-3 text-sm transition {{ request()->routeIs('admin.finance.collections.*') ? 'bg-[#AB1E23] font-medium text-[#E6EBF4]' : 'text-[#E6EBF4]/60 hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]' }}"
        >
          <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ request()->routeIs('admin.finance.collections.*') ? 'bg-[#E6C280]' : 'bg-[#E6EBF4]/40' }}"></span>
          {{ __('messages.finance_collections') }}
        </a>
        @endcan
        @can('finance_expenses.read')
        <a
          href="{{ route('admin.finance.expenses.index') }}"
          class="flex items-center gap-3 rounded py-2 pl-7 pr-3 text-sm transition {{ request()->routeIs('admin.finance.expenses.*') ? 'bg-[#AB1E23] font-medium text-[#E6EBF4]' : 'text-[#E6EBF4]/60 hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]' }}"
        >
          <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ request()->routeIs('admin.finance.expenses.*') ? 'bg-[#E6C280]' : 'bg-[#E6EBF4]/40' }}"></span>
          {{ __('messages.finance_expenses') }}
        </a>
        @endcan
        @can('finance_maintenance_charges.read')
        <a
          href="{{ route('admin.finance.maintenance-charges.index') }}"
          class="flex items-center gap-3 rounded py-2 pl-7 pr-3 text-sm transition {{ request()->routeIs('admin.finance.maintenance-charges.*') ? 'bg-[#AB1E23] font-medium text-[#E6EBF4]' : 'text-[#E6EBF4]/60 hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]' }}"
        >
          <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ request()->routeIs('admin.finance.maintenance-charges.*') ? 'bg-[#E6C280]' : 'bg-[#E6EBF4]/40' }}"></span>
          {{ __('messages.finance_maintenance_charges') }}
        </a>
        @endcan
        @can('finance_fund_setting.read')
        <a
          href="{{ route('admin.finance.fund-setting.show') }}"
          class="flex items-center gap-3 rounded py-2 pl-7 pr-3 text-sm transition {{ request()->routeIs('admin.finance.fund-setting.*') ? 'bg-[#AB1E23] font-medium text-[#E6EBF4]' : 'text-[#E6EBF4]/60 hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]' }}"
        >
          <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ request()->routeIs('admin.finance.fund-setting.*') ? 'bg-[#E6C280]' : 'bg-[#E6EBF4]/40' }}"></span>
          {{ __('messages.finance_fund_setting') }}
        </a>
        @endcan
      </div>
    </div>
    @endcan

    @can('workers.read')
    <a
      href="{{ route('admin.workers.index') }}"
      class="flex items-center gap-3 rounded px-3 py-2 text-sm font-medium transition {{ request()->routeIs('admin.workers.*') ? 'bg-[#AB1E23] text-[#E6EBF4]' : 'text-[#E6EBF4]/70 hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]' }}"
    >
      <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
      </svg>
      {{ __('messages.workers') }}
    </a>
    @endcan

    @if (auth()->user()->isMainMember())
    <a
      href="{{ route('admin.finance.my-payments.index') }}"
      class="flex items-center gap-3 rounded px-3 py-2 text-sm font-medium transition {{ request()->routeIs('admin.finance.my-payments.*') ? 'bg-[#AB1E23] text-[#E6EBF4]' : 'text-[#E6EBF4]/70 hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]' }}"
    >
      <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
      </svg>
      {{ __('messages.finance_my_payments') }}
    </a>
    @endif

    @can('landing_page.read')
    <div x-data="{ landingPageOpen: @json(request()->routeIs('admin.landing-page.*')) }">
      <button
        type="button"
        @click="landingPageOpen = !landingPageOpen"
        class="flex w-full items-center gap-3 rounded px-3 py-2 text-sm transition {{ request()->routeIs('admin.landing-page.*') ? 'bg-[#AB1E23]/20 font-medium text-[#E6EBF4]' : 'text-[#E6EBF4]/70 hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]' }}"
        :aria-expanded="landingPageOpen"
      >
        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
        </svg>
        <span class="flex-1 text-left">{{ __('messages.landing_page') }}</span>
        <svg
          class="h-4 w-4 shrink-0 transition-transform duration-200"
          :class="landingPageOpen ? 'rotate-90' : ''"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>

      <div
        x-show="landingPageOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="mt-1 space-y-1 pl-4"
      >
        @can('landing_page_directory_roles.read')
        <a
          href="{{ route('admin.landing-page.directory-roles.index') }}"
          class="flex items-center gap-3 rounded py-2 pl-7 pr-3 text-sm transition {{ request()->routeIs('admin.landing-page.directory-roles.*') ? 'bg-[#AB1E23] font-medium text-[#E6EBF4]' : 'text-[#E6EBF4]/60 hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]' }}"
        >
          <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ request()->routeIs('admin.landing-page.directory-roles.*') ? 'bg-[#E6C280]' : 'bg-[#E6EBF4]/40' }}"></span>
          {{ __('messages.directory_roles') }}
        </a>
        @endcan
        @can('landing_page_useful_directory.read')
        <a
          href="{{ route('admin.landing-page.useful-directory.index') }}"
          class="flex items-center gap-3 rounded py-2 pl-7 pr-3 text-sm transition {{ request()->routeIs('admin.landing-page.useful-directory.*') ? 'bg-[#AB1E23] font-medium text-[#E6EBF4]' : 'text-[#E6EBF4]/60 hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]' }}"
        >
          <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ request()->routeIs('admin.landing-page.useful-directory.*') ? 'bg-[#E6C280]' : 'bg-[#E6EBF4]/40' }}"></span>
          {{ __('messages.useful_directory') }}
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
        @can('settings_modules.read')
        <a
          href="{{ route('admin.settings.modules.index') }}"
          class="flex items-center gap-3 rounded py-2 pl-7 pr-3 text-sm transition {{ request()->routeIs('admin.settings.modules.*') ? 'bg-[#AB1E23] font-medium text-[#E6EBF4]' : 'text-[#E6EBF4]/60 hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]' }}"
        >
          <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ request()->routeIs('admin.settings.modules.*') ? 'bg-[#E6C280]' : 'bg-[#E6EBF4]/40' }}"></span>
          {{ __('messages.settings_modules') }}
        </a>
        @endcan
        @can('settings_permissions.read')
        <a
          href="{{ route('admin.settings.permissions.index') }}"
          class="flex items-center gap-3 rounded py-2 pl-7 pr-3 text-sm transition {{ request()->routeIs('admin.settings.permissions.*') ? 'bg-[#AB1E23] font-medium text-[#E6EBF4]' : 'text-[#E6EBF4]/60 hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]' }}"
        >
          <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ request()->routeIs('admin.settings.permissions.*') ? 'bg-[#E6C280]' : 'bg-[#E6EBF4]/40' }}"></span>
          {{ __('messages.settings_permissions') }}
        </a>
        @endcan
        @can('settings_roles.read')
        <a
          href="{{ route('admin.settings.roles.index') }}"
          class="flex items-center gap-3 rounded py-2 pl-7 pr-3 text-sm transition {{ request()->routeIs('admin.settings.roles.*') ? 'bg-[#AB1E23] font-medium text-[#E6EBF4]' : 'text-[#E6EBF4]/60 hover:bg-[#E6EBF4]/10 hover:text-[#E6EBF4]' }}"
        >
          <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ request()->routeIs('admin.settings.roles.*') ? 'bg-[#E6C280]' : 'bg-[#E6EBF4]/40' }}"></span>
          {{ __('messages.settings_roles') }}
        </a>
        @endcan
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
