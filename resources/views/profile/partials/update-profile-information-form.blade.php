<section>
    <header>
        <h2 class="h5 fw-semibold">
            {{ __('auth.informations_profil') }}
        </h2>

        <p class="small text-muted">
            {{ __('auth.mettre_a_jour_informations_profil') }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-3">
        @csrf
        @method('patch')

        <div class="mb-3">
            <x-input-label for="prenom" :value="__('auth.prenom')" />
            <x-text-input id="prenom" name="prenom" type="text" :value="old('prenom', $user->prenom)" required autofocus
                autocomplete="given-name" />
            <x-input-error :messages="$errors->get('prenom')" />
        </div>

        <div class="mb-3">
            <x-input-label for="nom" :value="__('auth.nom')" />
            <x-text-input id="nom" name="nom" type="text" :value="old('nom', $user->nom)" required
                autocomplete="family-name" />
            <x-input-error :messages="$errors->get('nom')" />
        </div>

        <div class="mb-3">
            <x-input-label for="email" :value="__('auth.email')" />
            <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)" required
                autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="small text-muted">
                        {{ __('auth.email_non_verifie') }}

                        <button form="send-verification" class="btn btn-link p-0 small">
                            {{ __('auth.texte_renvoyer_verification') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 small text-success">
                            {{ __('auth.confirmation_envoi_verification') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <x-primary-button>{{ __('auth.enregistrer') }}</x-primary-button>
            @if (session('status') === 'profile-updated')
                <p class="small text-muted ms-2" id="profile-updated-msg">{{ __('auth.enregistre') }}</p>
                <script>
                    (function () {
                        var el = document.getElementById('profile-updated-msg');
                        if (!el) return;
                        setTimeout(function () { el.style.display = 'none'; }, 2000);
                    })();
                </script>
            @endif
        </div>
    </form>
</section>