<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Prénom and Nom -->
        <div class="row">
            <div class="col-md-6">
                <x-input-label for="prenom" :value="__('auth.prenom')" />
                <x-text-input id="prenom" class="" type="text" name="prenom" :value="old('prenom')" required autofocus
                    autocomplete="given-name" />
                <x-input-error :messages="$errors->get('prenom')" />
            </div>
            <div class="col-md-6">
                <x-input-label for="nom" :value="__('auth.nom')" />
                <x-text-input id="nom" class="" type="text" name="nom" :value="old('nom')" required
                    autocomplete="family-name" />
                <x-input-error :messages="$errors->get('nom')" />
            </div>
        </div>

        <!-- Language preference -->
        <div class="mt-3">
            <x-input-label for="languePref" :value="__('Langue')" />
            <select id="languePref" name="languePref" class="form-select">
                <option value="fr" {{ old('languePref') === 'fr' ? 'selected' : '' }}>Français</option>
                <option value="en" {{ old('languePref') === 'en' ? 'selected' : '' }}>English</option>
            </select>
            <x-input-error :messages="$errors->get('languePref')" />
        </div>

        <!-- Email Address -->
        <div class="mt-3">
            <x-input-label for="email" :value="__('auth.email')" />
            <x-text-input id="email" class="" type="email" name="email" :value="old('email')" required
                autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <!-- Password -->
        <div class="mt-3">
            <x-input-label for="password" :value="__('auth.mot_de_passe')" />

            <x-text-input id="password" class="" type="password" name="password" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-3">
            <x-input-label for="password_confirmation" :value="__('auth.confirmer_mot_de_passe')" />

            <x-text-input id="password_confirmation" class="" type="password" name="password_confirmation" required
                autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <div class="d-flex justify-content-end mt-3">
            <a class="small text-decoration-underline text-muted me-3" href="{{ route('login') }}">
                {{ __('auth.deja_inscrit') }}
            </a>

            <x-primary-button class="">
                {{ __('auth.inscription') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>