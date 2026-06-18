@props([
    'href' => route('home'),
    'height' => 'h-12 sm:h-14',
])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center']) }}>
    <img
        src="{{ asset('Assets/Ak_Logo.png') }}"
        alt="{{ __('messages.brand_name') }} {{ __('messages.brand_tagline') }}"
        @class([$height, 'w-auto object-contain'])
    />
</a>
