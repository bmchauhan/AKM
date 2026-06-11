@php
    $flashToasts = session('toasts', []);

    $uniqueToasts = [];
    $seen = [];

    foreach ($flashToasts as $toast) {
        if (! is_array($toast) || empty($toast['message'])) {
            continue;
        }

        $key = ($toast['type'] ?? 'info') . '|' . $toast['message'];

        if (isset($seen[$key])) {
            continue;
        }

        $seen[$key] = true;
        $uniqueToasts[] = [
            'type' => $toast['type'] ?? 'info',
            'message' => $toast['message'],
        ];
    }

    session()->forget('toasts');
@endphp

@if (count($uniqueToasts) > 0)
    <script type="application/json" id="flash-toasts-json">@json($uniqueToasts)</script>
@endif
