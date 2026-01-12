<x-app-layout>
    @php
        $recaptchaSiteKey = config('services.recaptcha.site_key');
    @endphp
    @if($recaptchaSiteKey)
        @push('head-scripts')
            {{-- Chargement du script reCAPTCHA dans le head --}}
            {{-- Note de sécurité SRI: Google reCAPTCHA ne fournit pas de hash SRI (Subresource Integrity) public --}}
            {{-- pour leur script api.js car le contenu peut varier selon la configuration du site. --}}
            {{-- Le risque est accepté et limité car: --}}
            {{-- 1. Le script provient du domaine officiel sécurisé de Google (www.google.com) --}}
            {{-- 2. Le chargement est conditionnel (seulement si activé) --}}
            {{-- 3. L'attribut crossorigin="anonymous" est présent pour la sécurité CORS --}}
            {{-- Référence: https://developers.google.com/recaptcha/docs/display --}}
            <script src="https://www.google.com/recaptcha/api.js?hl={{ app()->getLocale() }}"
                    async
                    defer></script>
        @endpush
    @endif

    <div class="min-vh-100 d-flex flex-column justify-content-center align-items-center bg-light">

        <div class="auth-wrapper">
            <div class="auth-card">
                @isset($slot)
                    {{ $slot }}
                @endisset
            </div>
        </div>
    </div>
</x-app-layout>
