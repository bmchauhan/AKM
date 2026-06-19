@php
    $dialog = session('swal_dialog');

    if (is_array($dialog) && filled($dialog['message'] ?? null)) {
        $payload = [
            'title' => $dialog['title'] ?? '',
            'message' => $dialog['message'],
            'icon' => $dialog['icon'] ?? 'warning',
            'confirm_text' => $dialog['confirm_text'] ?? __('messages.swal_ok'),
            'code' => $dialog['code'] ?? null,
            'actions' => $dialog['actions'] ?? [],
        ];
    } else {
        $payload = null;
    }

    session()->forget('swal_dialog');
@endphp

@if ($payload)
    <script type="application/json" id="flash-swal-json">@json($payload)</script>
@endif
