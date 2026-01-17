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

        <!-- Date de naissance -->
        <div class="mt-3">
            <x-input-label for="dateNaissance-label" :value="__('auth.date_naissance')" />
            <div class="position-relative">
                <div class="d-flex align-items-center gap-2">
                    <div id="display-dateNaissance" class="fw-semibold me-1 presence-date-text flex-grow-1 auth-input" style="border: 1px solid #ced4da; border-radius: 4px; padding: 0.375rem 0.75rem; min-height: calc(1.5em + 0.75rem + 2px); cursor: pointer;">
                        {{ old('dateNaissance') ? \Carbon\Carbon::parse(old('dateNaissance'))->format('d/m/Y') : '' }}
                    </div>
                    <button type="button" id="open-dateNaissance" class="btn btn-link p-0 presence-date-btn flex-shrink-0" aria-label="Choisir la date" style="color: #6c757d;">
                        <i class="bi bi-chevron-down"></i>
                    </button>
                </div>
                <input type="date" id="dateNaissance" name="dateNaissance"
                    value="{{ old('dateNaissance') }}"
                    class="presence-date-input-hidden @error('dateNaissance') is-invalid @enderror">
                <div id="custom-calendar-dateNaissance" class="presence-calendar-dropdown"></div>
                <x-input-error :messages="$errors->get('dateNaissance')" />
            </div>
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
        // Script pour le calendrier date de naissance
        (function() {
            const input = document.getElementById('dateNaissance');
            if (!input) return;
            
            const out = document.getElementById('display-dateNaissance');
            const btn = document.getElementById('open-dateNaissance');
            const calendarDropdown = document.getElementById('custom-calendar-dateNaissance');
            let currentDate = input.value ? new Date(input.value + 'T00:00:00') : new Date();
            let calendarVisible = false;

            function formatDateToYYYYMMDD(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            function formatFr(dateStr) {
                if (!dateStr) return '';
                try {
                    const d = new Date(dateStr + 'T00:00:00');
                    const day = String(d.getDate()).padStart(2, '0');
                    const month = String(d.getMonth() + 1).padStart(2, '0');
                    const year = d.getFullYear();
                    return `${day}/${month}/${year}`;
                } catch (_) { return ''; }
            }

            function render() {
                if (input.value) {
                    out.textContent = formatFr(input.value);
                } else {
                    out.textContent = '';
                }
            }

            function renderCalendar() {
                const year = currentDate.getFullYear();
                const month = currentDate.getMonth();
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                const maxDate = today;
                const minDate = new Date();
                minDate.setFullYear(today.getFullYear() - 100);
                minDate.setHours(0, 0, 0, 0);
                
                const firstDay = new Date(year, month, 1);
                const startDate = new Date(firstDay);
                startDate.setDate(startDate.getDate() - startDate.getDay());
                
                const monthNames = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
                const dayNames = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
                
                const currentYear = today.getFullYear();
                const years = [];
                for (let y = currentYear; y >= currentYear - 100; y--) {
                    years.push(y);
                }
                
                let html = `<div class="presence-calendar">
                    <div class="presence-calendar-header">
                        <button type="button" class="presence-calendar-nav" id="prev-month-dateNaissance"><i class="bi bi-chevron-left"></i></button>
                        <div class="presence-calendar-title-group">
                            <select id="calendar-month-dateNaissance" class="presence-calendar-select">${monthNames.map((m, i) => `<option value="${i}" ${i === month ? 'selected' : ''}>${m}</option>`).join('')}</select>
                            <select id="calendar-year-dateNaissance" class="presence-calendar-select">${years.map(y => `<option value="${y}" ${y === year ? 'selected' : ''}>${y}</option>`).join('')}</select>
                        </div>
                        <button type="button" class="presence-calendar-nav" id="next-month-dateNaissance"><i class="bi bi-chevron-right"></i></button>
                    </div>
                    <div class="presence-calendar-weekdays">
                        ${dayNames.map(d => `<div class="presence-calendar-weekday">${d}</div>`).join('')}
                    </div>
                    <div class="presence-calendar-days">`;
                
                const current = new Date(startDate);
                const todayStr = formatDateToYYYYMMDD(today);
                for (let i = 0; i < 42; i++) {
                    const dateStr = formatDateToYYYYMMDD(current);
                    const isCurrentMonth = current.getMonth() === month;
                    const isToday = dateStr === todayStr;
                    const isFuture = current > maxDate;
                    const isBeforeMin = current < minDate;
                    const isSelected = dateStr === input.value;
                    
                    let classes = 'presence-calendar-day';
                    if (!isCurrentMonth) classes += ' presence-calendar-day-other';
                    if (isToday) classes += ' presence-calendar-day-today';
                    if (isSelected) classes += ' presence-calendar-day-selected';
                    if (isFuture || isBeforeMin) classes += ' presence-calendar-day-disabled';
                    
                    html += `<div class="${classes}" ${!isFuture && !isBeforeMin ? `data-date="${dateStr}"` : ''}>${current.getDate()}</div>`;
                    current.setDate(current.getDate() + 1);
                }
                
                html += `</div></div>`;
                calendarDropdown.innerHTML = html;
                
                document.getElementById('prev-month-dateNaissance').addEventListener('click', (e) => {
                    e.stopPropagation();
                    currentDate.setMonth(currentDate.getMonth() - 1);
                    renderCalendar();
                });
                
                document.getElementById('next-month-dateNaissance').addEventListener('click', (e) => {
                    e.stopPropagation();
                    if (new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 1) <= maxDate) {
                        currentDate.setMonth(currentDate.getMonth() + 1);
                        renderCalendar();
                    }
                });
                
                document.getElementById('calendar-month-dateNaissance').addEventListener('change', (e) => {
                    e.stopPropagation();
                    currentDate.setMonth(parseInt(e.target.value));
                    renderCalendar();
                });
                
                document.getElementById('calendar-year-dateNaissance').addEventListener('change', (e) => {
                    e.stopPropagation();
                    const newYear = parseInt(e.target.value);
                    const newDate = new Date(newYear, currentDate.getMonth(), 1);
                    if (newDate <= maxDate && newDate >= minDate) {
                        currentDate.setFullYear(newYear);
                        renderCalendar();
                    } else {
                        e.target.value = currentDate.getFullYear();
                    }
                });
                
                document.getElementById('calendar-month-dateNaissance').addEventListener('click', (e) => e.stopPropagation());
                document.getElementById('calendar-year-dateNaissance').addEventListener('click', (e) => e.stopPropagation());
                
                document.querySelectorAll('.presence-calendar-day[data-date]').forEach(day => {
                    day.addEventListener('click', (e) => {
                        e.stopPropagation();
                        input.value = day.getAttribute('data-date');
                        render();
                        calendarDropdown.classList.remove('show');
                        calendarVisible = false;
                    });
                });
            }

            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                calendarVisible = !calendarVisible;
                if (calendarVisible) {
                    if (input.value) {
                        currentDate = new Date(input.value + 'T00:00:00');
                    }
                    calendarDropdown.classList.add('show');
                    renderCalendar();
                } else {
                    calendarDropdown.classList.remove('show');
                }
            });

            document.addEventListener('click', function(e) {
                if (!btn.contains(e.target) && !calendarDropdown.contains(e.target)) {
                    calendarDropdown.classList.remove('show');
                    calendarVisible = false;
                }
            });

            input.addEventListener('change', () => {
                render();
            });

            render();
        })();
    </script>
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