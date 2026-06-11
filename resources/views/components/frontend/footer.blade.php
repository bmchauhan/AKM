<footer class="mt-auto border-t border-[#E6C280]/40 bg-[#080D21]">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
            <p class="text-sm leading-relaxed text-[#E6EBF4]/70">
                {{ __('messages.copyright', ['year' => date('Y')]) }}
            </p>
            <div class="flex gap-6">
                <a href="#" class="text-sm text-[#E6EBF4]/70 transition hover:text-[#E6C280]">{{ __('messages.privacy') }}</a>
                <a href="#" class="text-sm text-[#E6EBF4]/70 transition hover:text-[#E6C280]">{{ __('messages.terms') }}</a>
                <a href="#" class="text-sm text-[#E6EBF4]/70 transition hover:text-[#E6C280]">{{ __('messages.contact') }}</a>
            </div>
        </div>
    </div>
</footer>
