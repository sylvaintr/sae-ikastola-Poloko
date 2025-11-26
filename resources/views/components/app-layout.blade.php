@props(['header' => null])

{{-- Render the main layout and pass slot and header variables to it --}}
@include('layouts.app', ['slot' => $slot, 'header' => $header])
