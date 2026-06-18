@php
    $items = [
        ['label' => 'home', 'route' => 'home'],
        ['label' => 'news', 'route' => 'news'],
        ['label' => 'our_committee', 'route' => 'committee'],
        ['label' => 'useful_directory', 'route' => 'useful-directory'],
    ];
@endphp

<nav
    {{ $attributes->merge(['class' => 'flex min-w-0 items-center gap-0.5 overflow-x-clip sm:gap-1']) }}
    aria-label="{{ __('messages.main_navigation') }}"
>
    @foreach ($items as $item)
        <x-frontend.nav-link
            :href="route($item['route'])"
            :active="request()->routeIs($item['route'])"
        >
            {{ __('messages.'.$item['label']) }}
        </x-frontend.nav-link>
    @endforeach
</nav>
