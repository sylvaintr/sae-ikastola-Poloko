@props(['active'])

@php
    $classes = 'nav-link';
    if ($active ?? false) {
        $classes .= ' active';
    }
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>