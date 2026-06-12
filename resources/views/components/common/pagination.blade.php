@props([
    'paginator',
])

@if ($paginator->total() > 0)
    <nav
        role="navigation"
        aria-label="{{ __('messages.pagination_label') }}"
        {{ $attributes->merge(['class' => 'flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between']) }}
    >
        <p class="text-sm text-[#0F141E]/70">
            @if ($paginator->firstItem())
                {{ __('messages.pagination_showing', [
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                    'total' => $paginator->total(),
                ]) }}
            @else
                {{ __('messages.pagination_empty') }}
            @endif
        </p>

        @if ($paginator->hasPages())
            <div class="flex flex-wrap items-center gap-1">
                @if ($paginator->onFirstPage())
                    <span class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg border border-[#E6EBF4] px-2 text-sm text-[#0F141E]/30">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </span>
                @else
                    <a
                        href="{{ $paginator->previousPageUrl() }}"
                        class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg border border-[#E6EBF4] px-2 text-sm text-[#080D21] transition hover:border-[#E6C280] hover:bg-[#ECEAE1]"
                        aria-label="{{ __('messages.pagination_previous') }}"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                @endif

                @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                    @if ($page === $paginator->currentPage())
                        <span class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg bg-[#AB1E23] px-2 text-sm font-semibold text-[#E6EBF4]">
                            {{ $page }}
                        </span>
                    @else
                        <a
                            href="{{ $url }}"
                            class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg border border-[#E6EBF4] px-2 text-sm font-medium text-[#080D21] transition hover:border-[#E6C280] hover:bg-[#ECEAE1]"
                        >
                            {{ $page }}
                        </a>
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a
                        href="{{ $paginator->nextPageUrl() }}"
                        class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg border border-[#E6EBF4] px-2 text-sm text-[#080D21] transition hover:border-[#E6C280] hover:bg-[#ECEAE1]"
                        aria-label="{{ __('messages.pagination_next') }}"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                @else
                    <span class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg border border-[#E6EBF4] px-2 text-sm text-[#0F141E]/30">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                @endif
            </div>
        @endif
    </nav>
@endif
