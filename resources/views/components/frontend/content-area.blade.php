@props([
    'fullWidth' => false,
    'padding' => true,
])

<main {{ $attributes->merge(['class' => 'flex-1']) }}>
    <div @class([
        'mx-auto w-full',
        'max-w-7xl' => ! $fullWidth,
        'px-4 sm:px-6 lg:px-8' => $padding,
        'py-8 sm:py-10 lg:py-12' => $padding,
    ])>
        {{ $slot }}
    </div>
</main>
