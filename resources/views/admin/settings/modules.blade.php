<x-layouts.admin :pageTitle="__('messages.settings_modules')">
    <div class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-[#AB1E23]">{{ __('messages.settings') }}</p>
            <h2 class="mt-1 text-xl font-bold text-[#080D21]">{{ __('messages.settings_modules') }}</h2>
            <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.modules_subtitle') }}</p>
        </div>

        <div class="rounded-xl border border-[#E6EBF4] bg-[#E6EBF4]/40 px-4 py-3 text-sm text-[#0F141E]/80">
            {{ __('messages.modules_read_only_hint') }}
        </div>

        <div class="overflow-hidden rounded-xl border border-[#E6EBF4] bg-white">
            <div class="hidden border-b border-[#E6EBF4] bg-[#E6EBF4]/50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-[#080D21] md:grid md:grid-cols-12 md:gap-3">
                <div class="md:col-span-2">{{ __('messages.modules_name') }}</div>
                <div class="md:col-span-2">{{ __('messages.modules_slug') }}</div>
                <div class="md:col-span-5">{{ __('messages.modules_description') }}</div>
                <div class="md:col-span-1 text-center">{{ __('messages.modules_roles_count') }}</div>
                <div class="md:col-span-2 text-right">{{ __('messages.modules_status') }}</div>
            </div>

            @forelse ($modules as $module)
                <div class="border-b border-[#E6EBF4] px-4 py-4 last:border-b-0 md:grid md:grid-cols-12 md:items-center md:gap-3">
                    <div class="mb-2 text-sm font-semibold text-[#080D21] md:col-span-2 md:mb-0">{{ $module['name'] }}</div>
                    <div class="mb-2 font-mono text-xs text-[#0F141E]/70 md:col-span-2 md:mb-0">{{ $module['slug'] }}</div>
                    <div class="mb-2 text-sm text-[#0F141E]/80 md:col-span-5 md:mb-0">{{ $module['description'] ?: '—' }}</div>
                    <div class="mb-2 text-center text-sm text-[#0F141E] md:col-span-1 md:mb-0">{{ $module['roles_count'] }}</div>
                    <div class="flex justify-end md:col-span-2">
                        @if ($module['is_system'])
                            <span class="inline-flex rounded-full bg-[#E6C280]/30 px-2.5 py-0.5 text-xs font-semibold text-[#080D21]">
                                {{ __('messages.modules_system_badge') }}
                            </span>
                        @endif
                        <span class="ml-2 inline-flex rounded-full bg-[#E6EBF4] px-2.5 py-0.5 text-xs font-medium text-[#080D21]">
                            {{ __('messages.modules_read_only_badge') }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="px-4 py-10 text-center text-sm text-[#0F141E]/60">
                    {{ __('messages.modules_empty') }}
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.admin>
