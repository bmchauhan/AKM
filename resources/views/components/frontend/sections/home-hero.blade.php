<section class="relative w-full overflow-hidden border-b border-[#E6C280]/40 bg-gradient-to-br from-[#E6EBF4] via-[#ECEAE1] to-[#ECEAE1]">
    <div class="pointer-events-none absolute left-0 top-8 h-48 w-48 -translate-x-1/4 rounded-full bg-[#E6C280]/20 blur-3xl" aria-hidden="true"></div>
    <div class="pointer-events-none absolute right-0 bottom-4 h-52 w-52 translate-x-1/4 rounded-full bg-[#AB1E23]/8 blur-3xl" aria-hidden="true"></div>
    <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-transparent via-[#E6C280] to-transparent opacity-70" aria-hidden="true"></div>

    <div class="relative mx-auto grid max-w-7xl grid-cols-1 items-center gap-8 px-4 py-10 sm:px-6 sm:py-12 lg:grid-cols-2 lg:gap-10 lg:py-12">
        <div class="order-2 min-w-0 text-center lg:order-1 lg:text-left">
            <div class="mx-auto max-w-xl lg:mx-0">
                <p class="inline-flex rounded-full bg-[#AB1E23]/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-[#AB1E23] sm:text-sm">
                    {{ __('messages.hero_eyebrow') }}
                </p>

                <h1 class="mt-4 text-3xl font-bold leading-[1.15] text-[#080D21] sm:mt-5 sm:text-4xl lg:text-[2.65rem] xl:text-5xl">
                    {{ __('messages.hero_heading') }}
                </h1>

                <p class="mt-4 text-base leading-relaxed text-[#0F141E]/80 sm:mt-5 sm:text-lg">
                    {{ __('messages.hero_subtext') }}
                </p>

                <div class="mt-7 flex flex-col items-center gap-3 sm:mt-8 sm:flex-row sm:justify-center lg:justify-start">
                    <a
                        href="{{ route('useful-directory') }}"
                        class="inline-flex w-full items-center justify-center rounded-full bg-[#AB1E23] px-6 py-3 text-sm font-semibold text-[#E6EBF4] shadow-md ring-2 ring-[#AB1E23]/20 transition hover:bg-[#E6C280] hover:text-[#080D21] hover:ring-[#E6C280]/40 sm:w-auto"
                    >
                        {{ __('messages.hero_cta_directory') }}
                    </a>
                    <a
                        href="{{ route('news') }}"
                        class="inline-flex w-full items-center justify-center rounded-full border border-[#E6C280]/60 bg-white/80 px-6 py-3 text-sm font-semibold text-[#080D21] shadow-sm transition hover:border-[#AB1E23]/30 hover:bg-white sm:w-auto"
                    >
                        {{ __('messages.hero_cta_news') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="order-1 flex min-w-0 items-center justify-center overflow-hidden lg:order-2 lg:justify-end">
            <img
                src="{{ asset('Assets/Right Image.png') }}"
                alt="{{ __('messages.hero_image_alt') }}"
                class="h-auto w-full max-w-[26rem] object-contain sm:max-w-[30rem] lg:max-h-[min(58vh,32rem)] lg:max-w-full xl:max-h-[min(62vh,36rem)]"
                width="800"
                height="800"
                loading="eager"
                decoding="async"
            />
        </div>
    </div>
</section>
