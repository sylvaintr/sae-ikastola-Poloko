<section>
    <header>
        <h2 class="h5 fw-semibold">
            {{ __('auth.profile_information') }}
        </h2>

        <p class="small text-muted">
            {{ __('auth.update_profile_info') }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-3">
        @csrf
        @method('patch')

        <div class="mb-3">
            <x-input-label for="name" :value="__('auth.name')" />
            <x-text-input id="name" name="name" type="text" :value="old('name', $user->name)" required autofocus
                autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="mb-3">
            <x-input-label for="email" :value="__('auth.email')" />
            <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)" required
                autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="small text-muted">
                        {{ __('auth.email_unverified') }}

                        <button form="send-verification" class="btn btn-link p-0 small">
                            {{ __('auth.resend_verification') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 small text-success">
                            {{ __('auth.email_verification_sent') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <x-primary-button>{{ __('auth.save') }}</x-primary-button>
            @if (session('status') === 'profile-updated')
                <p class="small text-muted ms-2" id="profile-updated-msg">{{ __('auth.saved') }}</p>
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
</section>