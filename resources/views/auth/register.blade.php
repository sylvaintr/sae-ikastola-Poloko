<x-guest-layout>
    @php
        $recaptchaEnabled = config('services.recaptcha.enabled', true);
        $recaptchaSiteKey = config('services.recaptcha.site_key');
    @endphp

    <form method="POST"
        action="{{ route('register') }}"
        class="auth-form"
        id="register-form"
        data-recaptcha-enabled="{{ ($recaptchaEnabled && !empty($recaptchaSiteKey)) ? '1' : '0' }}"
        data-recaptcha-message="{{ __('auth.recaptcha_required') }}">
        @csrf

        <!-- Prénom and Nom -->
        <div class="row">
            <div class="col-md-6">
                <x-input-label for="prenom" :value="__('auth.prenom')" />
                <x-text-input id="prenom" class="auth-input" type="text" name="prenom" :value="old('prenom')" required autofocus
                    autocomplete="given-name" />
                <x-input-error :messages="$errors->get('prenom')" />
            </div>
            <div class="col-md-6">
                <x-input-label for="nom" :value="__('auth.nom')" />
                <x-text-input id="nom" class="auth-input" type="text" name="nom" :value="old('nom')" required
                    autocomplete="family-name" />
                <x-input-error :messages="$errors->get('nom')" />
            </div>
        </div>

        <!-- Language preference -->
        <div class="mt-3">
            <x-input-label for="languePref" :value="__('Langue')" />
            <select id="languePref" name="languePref" class="form-select auth-input">
                <option value="fr" {{ old('languePref') === 'fr' ? 'selected' : '' }}>Français</option>
                <option value="eus" {{ old('languePref') === 'eus' ? 'selected' : '' }}>Euskara</option>
            </select>
            <x-input-error :messages="$errors->get('languePref')" />
        </div>

        <!-- Email Address -->
        <div class="mt-3">
            <x-input-label for="email" :value="__('auth.email')" />
            <x-text-input id="email" class="auth-input" type="email" name="email" :value="old('email')" required
                autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <!-- Password -->
        <div class="mt-3">
            <x-input-label for="password" :value="__('auth.mot_de_passe')" />

            <x-text-input id="password" class="auth-input" type="password" name="password" required autocomplete="new-password" />
            
            <div id="password-requirements" class="mt-2">
                <div id="req-length" class="password-req-item text-danger">
                    <span class="req-icon">✗</span> {{ __('auth.password_req_length') }}
                </div>
                <div id="req-uppercase" class="password-req-item text-danger">
                    <span class="req-icon">✗</span> {{ __('auth.password_req_uppercase') }}
                </div>
                <div id="req-lowercase" class="password-req-item text-danger">
                    <span class="req-icon">✗</span> {{ __('auth.password_req_lowercase') }}
                </div>
                <div id="req-number" class="password-req-item text-danger">
                    <span class="req-icon">✗</span> {{ __('auth.password_req_number') }}
                </div>
                <div id="req-special" class="password-req-item text-danger">
                    <span class="req-icon">✗</span> {{ __('auth.password_req_special') }}
                </div>
            </div>

            <x-input-error :messages="$errors->get('password')" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-3">
            <x-input-label for="password_confirmation" :value="__('auth.confirmer_mot_de_passe')" />

            <x-text-input id="password_confirmation" class="auth-input" type="password" name="password_confirmation" required
                autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <!-- reCAPTCHA -->
        @if($recaptchaEnabled && !empty($recaptchaSiteKey))
        <div class="mt-3">
            <div class="g-recaptcha" data-sitekey="{{ $recaptchaSiteKey }}" data-callback="onRecaptchaSuccess" data-expired-callback="onRecaptchaExpired"></div>
            <x-input-error :messages="$errors->get('g-recaptcha-response')" />
        </div>
        @endif

        <div class="d-flex justify-content-end mt-3">
            <a class="small text-decoration-underline text-muted me-3" href="{{ route('login') }}">
                {{ __('auth.deja_inscrit') }}
            </a>

            <x-primary-button id="submit-button" type="submit">
                {{ __('auth.inscription') }}
            </x-primary-button>
        </div>
    </form>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const requirements = {
                'req-length': (pwd) => pwd.length >= 8,
                'req-uppercase': (pwd) => /[A-Z]/.test(pwd),
                'req-lowercase': (pwd) => /[a-z]/.test(pwd),
                'req-number': (pwd) => /[0-9]/.test(pwd),
                // Match Laravel Rules\Password::symbols() default symbol set:
                // !"#$%&'()*+,-./:;<=>?@[\]^_`{|}~
                'req-special': (pwd) => /[!"#$%&'()*+,\-./:;<=>?@\[\\\]^_`{|}~]/.test(pwd),
            };

            passwordInput.addEventListener('input', function() {
                const password = this.value;
                
                Object.keys(requirements).forEach(reqId => {
                    const reqElement = document.getElementById(reqId);
                    const reqIcon = reqElement.querySelector('.req-icon');
                    const isValid = requirements[reqId](password);
                    
                    if (isValid) {
                        reqElement.classList.remove('text-danger');
                        reqElement.classList.add('text-success');
                        reqIcon.textContent = '✓';
                    } else {
                        reqElement.classList.remove('text-success');
                        reqElement.classList.add('text-danger');
                        reqIcon.textContent = '✗';
                    }
                });
            });

            // Gestion du bouton submit avec reCAPTCHA
            const registerForm = document.getElementById('register-form');
            const recaptchaEnabled = registerForm?.dataset.recaptchaEnabled === '1';
            const recaptchaMessage = registerForm?.dataset.recaptchaMessage || 'Veuillez compléter la vérification reCAPTCHA avant de vous inscrire.';

            if (recaptchaEnabled) {
                const submitButton = document.getElementById('submit-button');
                let recaptchaValidated = false;

                // Désactiver le bouton tant que le captcha n'est pas validé
                if (submitButton) {
                    submitButton.disabled = true;
                }
                
                // Fonction appelée quand le reCAPTCHA est validé
                window.onRecaptchaSuccess = function() {
                    recaptchaValidated = true;
                    if (submitButton) {
                        submitButton.disabled = false;
                    }
                };

                // Fonction appelée quand le reCAPTCHA expire
                window.onRecaptchaExpired = function() {
                    recaptchaValidated = false;
                    if (submitButton) {
                        submitButton.disabled = true;
                    }
                };

                // Empêcher la soumission du formulaire si le captcha n'est pas validé
                if (registerForm) {
                    registerForm.addEventListener('submit', function(e) {
                        if (!recaptchaValidated) {
                            e.preventDefault();
                            alert(recaptchaMessage);
                            return false;
                        }
                    });
                }
            }
        });
    </script>
    @endpush
</x-guest-layout>