@props(['slot'])

{{-- Render the guest layout and pass through the slot --}}
@include('layouts.guest', ['slot' => $slot])