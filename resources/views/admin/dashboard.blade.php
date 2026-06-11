<x-layouts.admin>
    <p class="text-[#0F141E]">
        {{ __('messages.dashboard_welcome', ['name' => auth()->user()->name]) }}
    </p>
    <p class="mt-2 text-sm text-[#0F141E]/70">
        {{ __('messages.dashboard_role', ['role' => auth()->user()->role->label()]) }}
    </p>
</x-layouts.admin>
