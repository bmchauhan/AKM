<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('messages.login_title') }} | {{ __('messages.brand_name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="locale-{{ app()->getLocale() }} flex min-h-screen items-center justify-center bg-[#ECEAE1] px-4 py-12 text-[#0F141E] antialiased">
    <x-common.toast-bridge />

    <div class="w-full max-w-md">
        <div class="mb-8 text-center">
            <a href="{{ url('/') }}" class="inline-flex justify-center">
                <img
                    src="{{ asset('Assets/Ak_Logo.png') }}"
                    alt="{{ __('messages.brand_name') }}"
                    class="h-16 w-auto object-contain"
                />
            </a>
            <h1 class="mt-6 text-2xl font-bold text-[#080D21]">
                {{ __('messages.login_heading') }}
            </h1>
            <p class="mt-2 text-sm text-[#0F141E]/70">
                {{ __('messages.login_subtitle') }}
            </p>
        </div>

        <div class="rounded-2xl border border-[#E6EBF4] bg-white p-6 shadow-sm sm:p-8">
            <form method="POST" action="{{ route('login') }}" class="space-y-4" autocomplete="on" novalidate>
                @csrf

                <x-common.input
                    name="login"
                    :label="__('messages.login_field')"
                    :placeholder="__('messages.login_field_placeholder')"
                    :value="old('login')"
                    autocomplete="username"
                    autofocus
                />

                <x-common.password
                    name="password"
                    :label="__('messages.auth_password')"
                    :placeholder="__('messages.auth_password_placeholder')"
                    autocomplete="current-password"
                />

                <label class="flex items-center gap-2 text-sm text-[#0F141E]">
                    <input
                        type="checkbox"
                        name="remember"
                        value="1"
                        @checked(old('remember'))
                        class="rounded border-[#E6EBF4] text-[#AB1E23] focus:ring-[#AB1E23]/20"
                    />
                    {{ __('messages.login_remember') }}
                </label>

                <x-common.button type="submit" class="w-full">
                    {{ __('messages.login_submit') }}
                </x-common.button>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-[#0F141E]/60">
            <a href="{{ url('/') }}" class="font-medium text-[#AB1E23] transition hover:text-[#080D21]">
                &larr; {{ __('messages.back_to_home') }}
            </a>
        </p>
    </div>
</body>
</html>
