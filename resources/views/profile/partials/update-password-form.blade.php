<section>
    <header>
        <h2 class="h5 fw-semibold">
            {{ __('auth.mettre_a_jour_mot_de_passe') }}
        </h2>

        <p class="small text-muted">
            {{ __('auth.aide_mot_de_passe') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-3">
        @csrf
        @method('put')

        <div class="mb-3">
            <x-input-label for="update_password_current_password" :value="__('auth.mot_de_passe_actuel')" />
            <x-text-input id="update_password_current_password" name="current_password" type="password"
                autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" />
        </div>

        <div class="mb-3">
            <x-input-label for="update_password_password" :value="__('auth.nouveau_mot_de_passe')" />
            <x-text-input id="update_password_password" name="password" type="password" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" />
        </div>

        <div class="mb-3">
            <x-input-label for="update_password_password_confirmation" :value="__('auth.confirmer_mot_de_passe')" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password"
                autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" />
        </div>

        <div class="d-flex align-items-center gap-3">
            <x-primary-button>{{ __('auth.enregistrer') }}</x-primary-button>
            @if (session('status') === 'password-updated')
                <p class="small text-muted ms-2" id="password-updated-msg">{{ __('auth.enregistre') }}</p>
                <script>
                    (function () {
                        var el = document.getElementById('password-updated-msg');
                        if (!el) return;
                        setTimeout(function () { el.style.display = 'none'; }, 2000);
                    })();
                </script>
            @endif
        </div>
    </form>
</section>