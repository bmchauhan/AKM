@props([
    'title' => '',
    'maxWidth' => 'max-w-2xl',
])

<template x-teleport="body">
<div
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[100] flex items-end justify-center p-4 sm:items-center"
    role="dialog"
    aria-modal="true"
    x-on:keydown.escape.window="open = false"
>
    <div
        x-show="open"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-[#080D21]/50"
        x-on:click="open = false"
    ></div>

    <div
        x-show="open"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        {{ $attributes->merge(['class' => 'relative z-10 w-full rounded-xl border border-[#E6EBF4] bg-white shadow-xl '.$maxWidth]) }}
    >
        <div class="flex items-start justify-between gap-4 border-b border-[#E6EBF4] px-5 py-4">
            <h3 class="text-base font-bold text-[#080D21]" x-text="title"></h3>
            <button
                type="button"
                class="rounded-lg p-1 text-[#0F141E]/50 transition hover:bg-[#ECEAE1] hover:text-[#080D21]"
                x-on:click="open = false"
                aria-label="Close"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="max-h-[70vh] overflow-y-auto px-5 py-4">
            {{ $slot }}
        </div>
    </div>
</div>
</template>
