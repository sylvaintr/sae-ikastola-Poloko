<x-app-layout>
    @php
        $recaptchaSiteKey = config('services.recaptcha.site_key');
    @endphp
    @if($recaptchaSiteKey)
        @push('head-scripts')
            {{-- Chargement du script reCAPTCHA dans le head --}}
            <script src="https://www.google.com/recaptcha/api.js?hl={{ app()->getLocale() }}" async defer></script>
        @endpush
    @endif

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
    </div>
</x-app-layout>
