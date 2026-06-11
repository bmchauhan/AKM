@php
    $items = [
        ['label' => 'home', 'href' => '#home', 'section' => 'home'],
        ['label' => 'latest_updates', 'href' => '#latest-updates', 'section' => 'latest-updates'],
        ['label' => 'management_committee', 'href' => '#management-committee', 'section' => 'management-committee'],
        ['label' => 'facilities_gallery', 'href' => '#facilities-gallery', 'section' => 'facilities-gallery'],
        ['label' => 'directory', 'href' => '#member-directory', 'section' => 'member-directory'],
        ['label' => 'contact', 'href' => '#contact', 'section' => 'contact'],
    ];
@endphp

<nav
    {{ $attributes->merge(['class' => 'flex items-center gap-0.5 overflow-x-auto scrollbar-thin sm:gap-1']) }}
    aria-label="{{ __('messages.main_navigation') }}"
    data-nav
>
    @foreach ($items as $item)
        <x-frontend.nav-link
            :href="$item['href']"
            :section="$item['section']"
        >
            {{ __('messages.' . $item['label']) }}
        </x-frontend.nav-link>
    @endforeach
</nav>
