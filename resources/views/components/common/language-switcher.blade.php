<div class="relative inline-block text-left" x-data="{ open: false }">
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
        class="absolute right-0 z-50 mt-2 w-40 rounded border border-[#E6EBF4] bg-white shadow-lg"
    >
        <div class="py-1">
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
