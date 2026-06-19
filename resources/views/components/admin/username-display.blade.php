@props([
    'value' => '',
])

<div>
    <p class="text-sm font-medium text-[#080D21]">{{ __('messages.users_username') }}</p>
    <p class="mt-1 font-mono text-sm text-[#0F141E]">{{ $value }}</p>
    <p class="mt-1 text-xs text-[#0F141E]/50">{{ __('messages.users_username_readonly') }}</p>
</div>
