<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('auth.nom')" />
            <x-text-input id="name" class="" type="text" name="name" :value="old('name')" required autofocus
                autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
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