<x-guest-layout>
    <div class="mb-3 small text-muted">
        {{ __('auth.zone_securisee') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('auth.mot_de_passe')" />

            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="d-flex justify-content-end mt-3">
            <x-primary-button>
                {{ __('auth.confirmer') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>