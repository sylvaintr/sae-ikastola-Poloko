@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'alert alert-success small']) }} role="alert">
        {{ $status }}
    </div>
@endif