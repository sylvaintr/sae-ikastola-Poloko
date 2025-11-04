<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('auth.email')" />
            <x-text-input id="email" class="" type="email" name="email" :value="old('email')" required autofocus
                autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <!-- Password -->
        <div class="mt-3">
            <x-input-label for="password" :value="__('auth.mot_de_passe')" />
            <x-text-input id="password" class="" type="password" name="password" required
                autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" />
        </div>

        <!-- Remember Me -->
        <div class="form-check mt-3">
            <input id="remember_me" class="form-check-input" type="checkbox" name="remember">
            <label for="remember_me" class="form-check-label small">{{ __('auth.se_souvenir_de_moi') }}</label>
        </div>

        <div class="d-flex justify-content-end mt-3">
            @if (Route::has('password.request'))
                <a class="small text-decoration-underline text-muted me-3" href="{{ route('password.request') }}">
                    {{ __('auth.mot_de_passe_oublie') }}
                </a>
            @endif

            <x-primary-button class="">
                {{ __('auth.connexion') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>