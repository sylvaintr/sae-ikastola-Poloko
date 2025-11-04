<section class="mb-4">
    <header>
        <h2 class="h5 fw-semibold">
            {{ __('auth.delete_account') }}
        </h2>

        <p class="small text-muted">
            {{ __('auth.delete_account_notice') }}
        </p>
    </header>

    <x-danger-button
        onclick="event.preventDefault(); window.dispatchEvent(new CustomEvent('open-modal', { detail: 'confirm-user-deletion' }));">{{ __('auth.delete_account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-3">
            @csrf
            @method('delete')

            <h2 class="h6 fw-semibold">
                {{ __('auth.delete_account_confirm_title') }}
            </h2>

            <p class="small text-muted">
                {{ __('auth.delete_account_confirm_body') }}
            </p>

            <div class="mt-3 mb-3">

                <x-input-label for="password" value="{{ __('auth.password') }}" class="sr-only" />

                <x-text-input id="password" name="password" type="password" placeholder="{{ __('auth.password') }}" />

                <x-input-error :messages="$errors->userDeletion->get('password')" />
            </div>

            <div class="d-flex justify-content-end">
                <x-secondary-button
                    onclick="event.preventDefault(); window.dispatchEvent(new CustomEvent('close-modal', { detail: 'confirm-user-deletion' }));">
                    {{ __('auth.cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('auth.delete_account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>