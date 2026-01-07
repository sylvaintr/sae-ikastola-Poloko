<x-guest-layout>

    <div class="auth-header">
        <h1 class="auth-title">
            {{ __('auth.reinitialiser_mot_de_passe') }}
        </h1>
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('auth.email')" />
            <x-text-input id="email" type="email" value="{{ old('email', $request->email) }}" disabled readonly />
            <input type="hidden" name="email" value="{{ old('email', $request->email) }}">
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <!-- Password -->
        <div class="mt-3">
            <x-input-label for="password" :value="__('auth.mot_de_passe')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />

            {{-- Password strength --}}
            <div class="password-strength-container">
                <div class="password-strength-bar">
                    <div id="password-strength-fill" class="password-strength-fill"></div>
                </div>
                <div id="password-strength-text" class="password-strength-text">
                    {{ __('auth.password_strength_hint') }}
                </div>

                <small class="text-muted d-block mt-1">
                    {{ __('auth.password_requirements') }}
                </small>
            </div>
        </div>

        {{-- Password criteria --}}
        <ul class="list-unstyled mt-2 mb-1" id="password-criteria">
            <li data-rule="length">
                <span class="criterion-icon" aria-hidden="true"></span>
                <span class="criterion-label">{{ __('auth.password_rule_length') }}</span>
            </li>
            <li data-rule="lower">
                <span class="criterion-icon" aria-hidden="true"></span>
                <span class="criterion-label">{{ __('auth.password_rule_lower') }}</span>
            </li>
            <li data-rule="upper">
                <span class="criterion-icon" aria-hidden="true"></span>
                <span class="criterion-label">{{ __('auth.password_rule_upper') }}</span>
            </li>
            <li data-rule="number">
                <span class="criterion-icon" aria-hidden="true"></span>
                <span class="criterion-label">{{ __('auth.password_rule_number') }}</span>
            </li>
            <li data-rule="symbol">
                <span class="criterion-icon" aria-hidden="true"></span>
                <span class="criterion-label">{{ __('auth.password_rule_symbol') }}</span>
            </li>
        </ul>

        <!-- Confirm Password -->
        <div class="mt-3">
            <x-input-label for="password_confirmation" :value="__('auth.confirmer_mot_de_passe')" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required
                autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />

            {{-- Password match --}}
            <small id="password-match-text" class="d-block mt-2"></small>
        </div>


        <div class="d-flex justify-content-end mt-4">
            <x-primary-button id="reset-password-btn" disabled>
                {{ __('auth.reinitialiser_mot_de_passe') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
<script>
    window.passwordI18n = {
        empty: @json(__('auth.password_strength_empty')),
        weak: @json(__('auth.password_strength_weak')),
        medium: @json(__('auth.password_strength_medium')),
        strong: @json(__('auth.password_strength_strong')),
        veryStrong: @json(__('auth.password_strength_very_strong')),

        matchEmpty: @json(__('auth.password_match_empty')),
        matchOk: @json(__('auth.password_match_ok')),
        matchNo: @json(__('auth.password_match_no')),
    };
</script>

@vite('resources/js/password-reset.js')
