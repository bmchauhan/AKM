<div class="relative z-[60] inline-block text-left" x-data="{ open: false }">
    <button
        type="button"
        @click="open = !open"
        class="flex items-center gap-2 rounded-full border border-[#E6C280]/60 bg-[#E6EBF4] px-3 py-2 text-sm font-semibold text-[#080D21] shadow-sm transition hover:border-[#E6C280] hover:bg-white hover:shadow"
    >
        <span>🌐</span>
        <span>{{ strtoupper(app()->getLocale()) }}</span>
        <svg class="h-4 w-4 text-[#080D21]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <div
        x-show="open"
        @click.away="open = false"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        class="absolute right-0 z-[100] mt-2 w-44 origin-top-right rounded-lg border border-[#E6EBF4] bg-white py-1 shadow-lg ring-1 ring-[#080D21]/5"
    >
        <div class="py-0.5">
            <a
                href="{{ route('lang.switch', 'en') }}"
                class="block px-4 py-2 text-sm text-[#0F141E] hover:bg-[#E6EBF4] {{ app()->getLocale() == 'en' ? 'bg-[#ECEAE1] font-bold text-[#080D21]' : '' }}"
            >
                {{ __('messages.language_english') }}
            </a>
            <a
                href="{{ route('lang.switch', 'hi') }}"
                class="block px-4 py-2 text-sm text-[#0F141E] hover:bg-[#E6EBF4] {{ app()->getLocale() == 'hi' ? 'bg-[#ECEAE1] font-bold text-[#080D21]' : '' }}"
            >
                {{ __('messages.language_hindi') }}
            </a>
            <a
                href="{{ route('lang.switch', 'gu') }}"
                class="block px-4 py-2 text-sm text-[#0F141E] hover:bg-[#E6EBF4] {{ app()->getLocale() == 'gu' ? 'bg-[#ECEAE1] font-bold text-[#080D21]' : '' }}"
            >
                {{ __('messages.language_gujarati') }}
            </a>
        </div>
    </div>
</div>
