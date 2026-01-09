<x-app-layout>
    @push('scripts')
        @php
            $recaptchaSiteKey = config('services.recaptcha.site_key');
        @endphp
        @if($recaptchaSiteKey)
            {{-- Chargement conditionnel et sécurisé de reCAPTCHA --}}
            {{-- Note de sécurité: Google reCAPTCHA ne fournit pas de hash SRI (Subresource Integrity) public --}}
            {{-- pour leur script api.js car le contenu peut varier selon la configuration. --}}
            {{-- Le risque est limité car le script provient du domaine officiel de Google (www.google.com) --}}
            {{-- et est chargé uniquement lorsque reCAPTCHA est activé et configuré. --}}
            <script src="https://www.google.com/recaptcha/api.js?hl={{ app()->getLocale() }}" async defer crossorigin="anonymous"></script>
        @endif
    @endpush
    
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
