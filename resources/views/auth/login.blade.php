<x-guest-layout>
    <div class="auth-header text-center">
        <h1 class="auth-title">{{ __('auth.titre_connexion_principal') }}</h1>
    </div>

    <x-auth-session-status class="auth-status" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="auth-form">
        @csrf

        <div class="auth-field">
            <label for="email" class="auth-field-label">{{ __('auth.email_principal') }}</label>
            <x-text-input id="email" class="auth-input" type="email" name="email" :value="old('email')" required
                autofocus autocomplete="username" placeholder="{{ __('auth.placeholder_email') }}" />
            <x-input-error class="auth-error" :messages="$errors->get('email')" />
        </div>

        <div class="auth-field">
            <label for="password" class="auth-field-label">{{ __('auth.mot_de_passe_principal') }}</label>
            <x-text-input id="password" class="auth-input" type="password" name="password" required
                autocomplete="current-password" placeholder="{{ __('auth.placeholder_password') }}" />
            <x-input-error class="auth-error" :messages="$errors->get('password')" />
        </div>

        <div class="auth-checkbox">
            <input id="remember_me" class="form-check-input" type="checkbox" name="remember">
            <label for="remember_me" class="auth-checkbox-label">{{ __('auth.se_souvenir_de_moi') }}</label>
        </div>

        @if (Route::has('password.request'))
            <div class="auth-forgot">
                <a class="auth-forgot-link" href="{{ route('password.request') }}">
                    <span class="auth-forgot-label">{{ __('auth.mot_de_passe_oublie_principal') }}</span>
                </a>
            </div>
        @endif

        <div class="auth-footer">
            <div class="auth-footer-text">
                <span class="auth-footer-title">{{ __('auth.pas_compte_principal') }}</span>
                @if (Route::has('register'))
                    <a class="auth-footer-link" href="{{ route('register') }}">{{ __('auth.pas_compte_secondaire') }}</a>
                @endif
            </div>

            <x-primary-button class="btn-login">
                {{ __('auth.bouton_connexion') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>