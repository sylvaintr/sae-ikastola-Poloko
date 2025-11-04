<x-guest-layout>
    <div class="mb-3 small text-muted">
        {{ __('auth.aide_mot_de_passe_oublie') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="d-flex justify-content-end mt-3">
            <x-primary-button>
                {{ __('auth.envoyer_lien_reinitialisation') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>