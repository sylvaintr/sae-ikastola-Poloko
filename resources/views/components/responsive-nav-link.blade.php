@props(['active'])

@php
    $classes = 'nav-link d-block py-2';
    if ($active ?? false) {
        $classes .= ' active';
    }
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>