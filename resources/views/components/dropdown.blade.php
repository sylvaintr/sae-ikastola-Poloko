@props(['align' => 'right', 'contentClasses' => 'py-1 bg-white dark:bg-gray-700'])

@php
    // Map simple alignment options to Bootstrap dropdown classes.
    $isDropup = $align === 'top';
    $menuAlignment = $align === 'left' ? 'dropdown-menu-start' : '';
    $dropClass = $isDropup ? 'dropup' : 'dropdown';
@endphp

<div class="{{ $dropClass }}">
    <div class="d-inline-block" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        {{ $trigger }}
    </div>

    <div class="dropdown-menu {{ $menuAlignment }} {{ $contentClasses }}">
        {{ $content }}
    </div>
</div>