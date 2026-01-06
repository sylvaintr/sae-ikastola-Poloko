<x-guest-layout>
    <div class="auth-header text-center">
        <h1 class="auth-title">{{ __('auth.connexion') }}</h1>
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

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
                <input id="remember_me" class="form-check-input" type="checkbox" name="remember">
                <label for="remember_me" class="form-check-label">
                    {{ __('auth.se_souvenir_de_moi') }}
                </label>
            </div>

            @if (Route::has('password.request'))
                <a class="auth-footer-link text-decoration-none" href="{{ route('password.request') }}">
                    {{ __('auth.mot_de_passe_oublie_principal') }}
                </a>
            @endif
        </div>

        <div class="mb-3">
            <x-primary-button class="btn-login w-100">
                {{ __('auth.connexion') }}
            </x-primary-button>
        </div>

        <div class="auth-footer">
            <div class="auth-footer-text">
                <span class="auth-footer-title">{{ __('auth.pas_compte_principal') }}</span>
                @if (Route::has('register'))
                    <a class="auth-footer-link"
                        href="{{ route('register') }}">{{ __('auth.pas_compte_secondaire') }}</a>
                @endif
            </div>
        </div>
    </form>
</x-guest-layout>
