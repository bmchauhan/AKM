@props([
    'action',
    'message',
    'title' => null,
])

<form
    method="POST"
    action="{{ $action }}"
    data-confirm-delete
    data-confirm-title="{{ $title ?? __('messages.delete_confirm_title') }}"
    data-confirm-message="{{ $message }}"
    data-confirm-yes="{{ __('messages.delete_confirm_yes') }}"
    data-confirm-cancel="{{ __('messages.delete_confirm_cancel') }}"
    {{ $attributes->merge(['class' => 'inline-flex']) }}
>
    @csrf
    @method('DELETE')
    {{ $slot }}
</form>
