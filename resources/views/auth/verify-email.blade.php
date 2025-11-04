<x-guest-layout>
    <div class="mb-3 small text-muted">
        {{ __('auth.verify_email_notice') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 alert alert-success small">
            {{ __('auth.email_verification_sent') }}
        </div>
    @endif

    <div class="mt-3 d-flex justify-content-between align-items-center">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('auth.resend_verification') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="btn btn-link small text-muted">
                {{ __('auth.logout') }}
            </button>
        </form>
    </div>
</x-guest-layout>