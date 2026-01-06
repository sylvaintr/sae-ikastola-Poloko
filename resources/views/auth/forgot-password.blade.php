<x-guest-layout>
    <div class="auth-header text-center mb-3">
        <h1 class="auth-title">
            {{ __('auth.reinitialiser_mot_de_passe') }}
        </h1>
    </div>

    <div class="mb-3 small text-muted">
        {{ __('auth.aide_mot_de_passe_oublie') }}
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" id="forgot-password-form">
        @csrf

        <div>
            <x-input-label for="email" :value="__('auth.email_principal')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="mt-3">
            <x-primary-button class="btn-login w-100" id="submit-btn" disabled>
                {{ __('auth.envoyer_lien_reinitialisation') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
