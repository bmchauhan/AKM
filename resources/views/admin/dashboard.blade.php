<x-layouts.admin :pageTitle="__('messages.dashboard')">
  @php
      $committeeSection = collect($sections)->first(
          fn ($section) => ($section['key'] ?? '') === 'committee_duty'
      );
      $otherSections = collect($sections)
          ->reject(fn ($section) => ($section['key'] ?? '') === 'committee_duty')
          ->values();
      $showPairedTables = ! empty($quick_actions) && filled($committeeSection);
  @endphp

    <div class="space-y-8">
        <div class="rounded-xl border border-[#E6C280]/40 bg-gradient-to-r from-[#E6EBF4]/90 via-white to-[#ECEAE1]/50 px-5 py-5 shadow-sm sm:p-6">
            <p class="text-xs font-semibold uppercase tracking-wider text-[#AB1E23]">{{ __('messages.dashboard') }}</p>
            <p class="mt-1 text-lg font-bold text-[#080D21] sm:text-xl">
                {{ __('messages.dashboard_welcome', ['name' => auth()->user()->fullName()]) }}
            </p>
            <p class="mt-1 text-sm text-[#0F141E]/70">
                {{ __('messages.dashboard_role', ['role' => auth()->user()->roleLabel()]) }}
            </p>
            @if (auth()->user()->houseLabel() && (auth()->user()->isMainMember() || auth()->user()->isFamilyMember() || auth()->user()->isRentalMember()))
                <p class="mt-2 inline-flex rounded-full bg-[#E6C280]/25 px-3 py-1 text-xs font-semibold text-[#080D21]">
                    {{ auth()->user()->houseLabel() }}
                </p>
            @endif
        </div>

        @if (! empty($alerts))
            <div class="space-y-3">
                @foreach ($alerts as $alert)
                    <div @class([
                        'flex flex-col gap-3 rounded-xl border px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5',
                        'border-[#E5989B]/40 bg-[#E5989B]/10' => ($alert['tone'] ?? '') === 'warning',
                        'border-[#E6C280]/50 bg-[#E6C280]/15' => ($alert['tone'] ?? '') === 'info',
                        'border-[#E6EBF4] bg-white' => ($alert['tone'] ?? '') === 'neutral',
                    ])>
                        <p class="text-sm text-[#080D21]">{{ $alert['message'] }}</p>
                        @if (! empty($alert['action_route']))
                            <a href="{{ $alert['action_route'] }}" class="shrink-0 text-sm font-semibold text-[#AB1E23] hover:text-[#080D21]">
                                {{ $alert['action_label'] }} →
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if (! empty($quick_actions) || filled($committeeSection))
            <div @class([
                'grid gap-5 items-stretch',
                'lg:grid-cols-2' => $showPairedTables,
            ])>
                @if (! empty($quick_actions))
                    <section class="flex min-w-0 flex-col gap-3">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-[#080D21]">
                            {{ __('messages.dashboard_quick_actions') }}
                        </h2>
                        <x-admin.snap-table
                            compact
                            class="flex-1"
                            :columns="[
                                ['key' => 'summary', 'label' => __('messages.dashboard_col_link')],
                                ['key' => 'action', 'label' => __('messages.dashboard_col_action'), 'align' => 'right'],
                            ]"
                            :rows="collect($quick_actions)->map(fn ($action) => [
                                'label' => $action['label'],
                                'hint' => $action['description'] ?? null,
                                'link' => $action['route'],
                                'action_label' => __('messages.dashboard_open_link'),
                            ])->all()"
                        />
                    </section>
                @endif

                @if (filled($committeeSection))
                    <section class="flex min-w-0 flex-col gap-3">
                        <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                            <div class="min-w-0">
                                <h2 class="text-sm font-bold uppercase tracking-wide text-[#080D21]">
                                    {{ $committeeSection['title'] }}
                                </h2>
                                @if (! empty($committeeSection['subtitle']))
                                    <p class="mt-1 line-clamp-2 text-sm text-[#0F141E]/60">{{ $committeeSection['subtitle'] }}</p>
                                @endif
                            </div>
                            @if (! empty($committeeSection['link']))
                                <a href="{{ $committeeSection['link'] }}" class="shrink-0 text-xs font-semibold text-[#AB1E23] hover:text-[#080D21]">
                                    {{ $committeeSection['link_label'] }} →
                                </a>
                            @endif
                        </div>
                        <x-admin.snap-table
                            compact
                            class="flex-1"
                            :columns="[
                                ['key' => 'summary', 'label' => __('messages.dashboard_col_module')],
                                ['key' => 'action', 'label' => __('messages.dashboard_col_action'), 'align' => 'right'],
                            ]"
                            :rows="collect($committeeSection['stats'])->map(fn ($stat) => [
                                'label' => $stat['label'],
                                'value' => $stat['value'],
                                'hint' => $stat['hint'] ?? null,
                                'tone' => $stat['tone'] ?? null,
                                'link' => $stat['link'] ?? null,
                                'is_granted' => str_ends_with((string) ($stat['key'] ?? ''), '_access'),
                                'action_label' => __('messages.dashboard_open_link'),
                            ])->all()"
                        />
                    </section>
                @endif
            </div>
        @endif

        @foreach ($otherSections as $section)
            <section class="space-y-4">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-sm font-bold uppercase tracking-wide text-[#080D21]">
                            {{ $section['title'] }}
                        </h2>
                        @if (! empty($section['subtitle']))
                            <p class="mt-1 text-sm text-[#0F141E]/60">{{ $section['subtitle'] }}</p>
                        @endif
                    </div>
                    @if (! empty($section['link']))
                        <a href="{{ $section['link'] }}" class="text-xs font-semibold text-[#AB1E23] hover:text-[#080D21]">
                            {{ $section['link_label'] }} →
                        </a>
                    @endif
                </div>

                <div @class([
                    'grid gap-5',
                    'sm:grid-cols-2 lg:grid-cols-3' => count($section['stats']) !== 4,
                    'sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-4' => count($section['stats']) === 4,
                ])>
                    @foreach ($section['stats'] as $stat)
                        <x-admin.stat-card
                            :label="$stat['label']"
                            :value="$stat['value']"
                            :hint="$stat['hint'] ?? null"
                            :tone="$stat['tone'] ?? 'neutral'"
                            :trend="$stat['trend'] ?? null"
                            class="min-h-[8.5rem]"
                        />
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</x-layouts.admin>
