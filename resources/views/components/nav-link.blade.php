@props(['active'])

@php
$classes = ($active ?? false)
            ? 'nav-pill nav-pill-active'
            : 'nav-pill';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
