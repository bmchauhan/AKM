@props([
    'title' => null,
    'pageTitle' => null,
])

@php
    $resolvedPageTitle = $pageTitle ?? __('messages.dashboard');
    $resolvedTitle = $title ?? __('messages.admin_panel') . ' | ' . __('messages.brand_name');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $resolvedTitle }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{ $head ?? '' }}
</head>
<body class="locale-{{ app()->getLocale() }} min-h-screen bg-[#ECEAE1] text-[#0F141E] antialiased">
    <x-common.toast-bridge />

    <div class="flex min-h-screen">
        @include('partials.admin.sidebar')

        <div class="flex min-w-0 flex-1 flex-col">
            @include('partials.admin.navbar', ['pageTitle' => $resolvedPageTitle])

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @isset($header)
                    <div class="mb-6">
                        {{ $header }}
                    </div>
                @endisset

                <div class="rounded-lg bg-[#E6EBF4] p-4 shadow-sm sm:p-6">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    {{ $scripts ?? '' }}
</body>
</html>
