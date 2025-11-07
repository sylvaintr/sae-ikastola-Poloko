<x-app-layout>
    <div class="min-vh-100 d-flex flex-column justify-content-center align-items-center bg-light">
        <div class="text-center mb-4">
            <a href="/">
                <x-application-logo class="" />
            </a>
        </div>

        <div class="auth-wrapper">
            <div class="auth-card">
                @isset($slot)
                    {{ $slot }}
                @endisset
            </div>
        </div>


        </x-a>
