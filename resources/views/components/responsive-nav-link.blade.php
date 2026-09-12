@props(['active'])

@php
$classes = ($active ?? false)
            ? 'mobile-nav-pill mobile-nav-pill-active'
            : 'mobile-nav-pill';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
