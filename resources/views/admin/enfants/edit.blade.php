<x-app-layout>
    <div class="container py-4">

        {{-- Header : titre + retour --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h4 fw-bold mb-1">
                    {{ __('enfants.title', [], 'eus') }}
                </h1>

                @if (Lang::getLocale() == 'fr')
                    <p class="text-muted small mb-0">
                        {{ __('enfants.title') }}
                    </p>
                @endif
            </div>

            <div class="text-end">
                <a href="{{ route('admin.enfants.index') }}" class="text-decoration-none fw-semibold text-warning">
                    ← {{ __('enfants.back_to_list', [], 'eus') }}
                    @if (Lang::getLocale() == 'fr')
                        <span class="d-block small fw-semibold text-warning">
                            {{ __('enfants.back_to_list') }}
                        </span>
                    @endif
                </a>
            </div>
        </div>

        {{-- Messages d'erreur --}}
        @if ($errors->any())
            <div class="alert alert-danger small">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $erreur)
                        <li>{{ $erreur }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Carte formulaire --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">

                <h2 class="h6 fw-semibold mb-3">
                    {{ __('enfants.edit_title', [], 'eus') }}
                    @if (Lang::getLocale() == 'fr')
                        <span class="d-block fw-light text-muted">
                            {{ __('enfants.edit_title') }}
                        </span>
                    @endif
                </h2>

                <form action="{{ route('admin.enfants.update', $enfant->idEnfant) }}" method="POST" class="small">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        {{-- Nom --}}
                        <div class="col-md-6 mb-3">
                            <label for="nom" class="form-label mb-1">
                                {{ __('enfants.nom', [], 'eus') }}
                                @if (Lang::getLocale() == 'fr')
                                    <span class="d-block text-muted fw-light">
                                        {{ __('enfants.nom') }}
                                    </span>
                                @endif
                            </label>
                            <input type="text" id="nom" name="nom"
                                value="{{ old('nom', $enfant->nom) }}"
                                class="form-control @error('nom') is-invalid @enderror" required maxlength="20">
                            @error('nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Prénom --}}
                        <div class="col-md-6 mb-3">
                            <label for="prenom" class="form-label mb-1">
                                {{ __('enfants.prenom', [], 'eus') }}
                                @if (Lang::getLocale() == 'fr')
                                    <span class="d-block text-muted fw-light">
                                        {{ __('enfants.prenom') }}
                                    </span>
                                @endif
                            </label>
                            <input type="text" id="prenom" name="prenom"
                                value="{{ old('prenom', $enfant->prenom) }}"
                                class="form-control @error('prenom') is-invalid @enderror" required maxlength="150">
                            @error('prenom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Date de naissance --}}
                        <div class="col-md-6 mb-3">
                            <label for="dateN-label" class="form-label mb-1">
                                {{ __('enfants.dateN', [], 'eus') }}
                                @if (Lang::getLocale() == 'fr')
                                    <span class="d-block text-muted fw-light">
                                        {{ __('enfants.dateN') }}
                                    </span>
                                @endif
                            </label>
                            <div class="position-relative">
                                <div class="d-flex align-items-center gap-2">
                                    <div id="display-dateN" class="fw-semibold me-1 presence-date-text flex-grow-1" style="border: 1px solid #ced4da; border-radius: 4px; padding: 0.375rem 0.75rem; min-height: calc(1.5em + 0.75rem + 2px);">
                                        {{ old('dateN', $enfant->dateN ? $enfant->dateN->format('d/m/Y') : '') }}
                                    </div>
                                    <button type="button" id="open-dateN" class="btn btn-outline-secondary flex-shrink-0" aria-label="Choisir la date" style="padding: 0.375rem 0.75rem;">
                                        <i class="bi bi-calendar"></i>
                                    </button>
                                </div>
                                <input type="date" id="dateN" name="dateN"
                                    value="{{ old('dateN', $enfant->dateN ? $enfant->dateN->format('Y-m-d') : '') }}"
                                    class="presence-date-input-hidden @error('dateN') is-invalid @enderror" required>
                                <div id="custom-calendar-dateN" class="presence-calendar-dropdown"></div>
                                @error('dateN')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- NNI --}}
                        <div class="col-md-6 mb-3">
                            <label for="NNI" class="form-label mb-1">
                                {{ __('enfants.NNI', [], 'eus') }}
                                @if (Lang::getLocale() == 'fr')
                                    <span class="d-block text-muted fw-light">
                                        {{ __('enfants.NNI') }}
                                    </span>
                                @endif
                            </label>
                            <input type="text" id="NNI" name="NNI"
                                value="{{ old('NNI', $enfant->NNI) }}"
                                class="form-control @error('NNI') is-invalid @enderror"
                                required
                                pattern="[0-9]{10}" 
                                maxlength="10"
                                inputmode="numeric">
                            @error('NNI')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Sexe --}}
                        <div class="col-md-6 mb-3">
                            <label for="sexe" class="form-label mb-1">
                                {{ __('enfants.sexe', [], 'eus') }}
                                @if (Lang::getLocale() == 'fr')
                                    <span class="d-block text-muted fw-light">
                                        {{ __('enfants.sexe') }}
                                    </span>
                                @endif
                            </label>
                            <select id="sexe" name="sexe"
                                class="form-select @error('sexe') is-invalid @enderror" required>
                                <option value="">{{ __('enfants.select_sexe', [], 'eus') }}</option>
                                <option value="M" @selected(old('sexe', $enfant->sexe) === 'M')>
                                    {{ __('enfants.garcon', [], 'eus') }}@if (Lang::getLocale() == 'fr') ({{ __('enfants.garcon') }})@endif
                                </option>
                                <option value="F" @selected(old('sexe', $enfant->sexe) === 'F')>
                                    {{ __('enfants.fille', [], 'eus') }}@if (Lang::getLocale() == 'fr') ({{ __('enfants.fille') }})@endif
                                </option>
                            </select>
                            @error('sexe')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Classe --}}
                        <div class="col-md-6 mb-3">
                            <label for="idClasse" class="form-label mb-1">
                                {{ __('enfants.classe', [], 'eus') }}
                                @if (Lang::getLocale() == 'fr')
                                    <span class="d-block text-muted fw-light">
                                        {{ __('enfants.classe') }}
                                    </span>
                                @endif
                            </label>
                            <select id="idClasse" name="idClasse"
                                class="form-select @error('idClasse') is-invalid @enderror">
                                <option value="">{{ __('enfants.no_classe') }}</option>
                                @foreach($classes as $classe)
                                    <option value="{{ $classe->idClasse }}" @selected(old('idClasse', $enfant->idClasse) == $classe->idClasse)>
                                        {{ $classe->nom }} ({{ $classe->niveau }})
                                    </option>
                                @endforeach
                            </select>
                            @error('idClasse')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Boutons d'action --}}
                    <div class="d-flex justify-content-end pt-3 mt-4">
                        <a href="{{ route('admin.enfants.index') }}" class="btn btn-link text-muted me-2 px-2">
                            {{ __('enfants.cancel', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr')
                                <span class="d-block small fw-light">
                                    {{ __('enfants.cancel') }}
                                </span>
                            @endif
                        </a>

                        <div class="d-flex flex-column align-items-end">
                            <button type="submit" class="btn btn-warning fw-semibold" style="padding: 0.375rem 1rem; font-size: 0.875rem; border-radius: 4px;">
                                {{ __('enfants.save', [], 'eus') }}
                            </button>
                            @if (Lang::getLocale() == 'fr')
                                <small class="text-muted mt-1" style="font-size: 0.75rem;">
                                    {{ __('enfants.save') }}
                                </small>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    (function() {
        const input = document.getElementById('dateN');
        const out = document.getElementById('display-dateN');
        const btn = document.getElementById('open-dateN');
        const calendarDropdown = document.getElementById('custom-calendar-dateN');
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
            
            // Date maximale : aujourd'hui
            const maxDate = today;
            // Date minimale : il y a 30 ans (pour les dates de naissance)
            const minDate = new Date();
            minDate.setFullYear(today.getFullYear() - 30);
            minDate.setHours(0, 0, 0, 0);
            
            const firstDay = new Date(year, month, 1);
            const startDate = new Date(firstDay);
            startDate.setDate(startDate.getDate() - startDate.getDay());
            
            const monthNames = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
            const dayNames = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
            
            const currentYear = today.getFullYear();
            const years = [];
            for (let y = currentYear; y >= currentYear - 30; y--) {
                years.push(y);
            }
            
            let html = `<div class="presence-calendar">
                <div class="presence-calendar-header">
                    <button type="button" class="presence-calendar-nav" id="prev-month-dateN"><i class="bi bi-chevron-left"></i></button>
                    <div class="presence-calendar-title-group">
                        <select id="calendar-month-dateN" class="presence-calendar-select">${monthNames.map((m, i) => `<option value="${i}" ${i === month ? 'selected' : ''}>${m}</option>`).join('')}</select>
                        <select id="calendar-year-dateN" class="presence-calendar-select">${years.map(y => `<option value="${y}" ${y === year ? 'selected' : ''}>${y}</option>`).join('')}</select>
                    </div>
                    <button type="button" class="presence-calendar-nav" id="next-month-dateN"><i class="bi bi-chevron-right"></i></button>
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
            
            document.getElementById('prev-month-dateN').addEventListener('click', (e) => {
                e.stopPropagation();
                currentDate.setMonth(currentDate.getMonth() - 1);
                renderCalendar();
            });
            
            document.getElementById('next-month-dateN').addEventListener('click', (e) => {
                e.stopPropagation();
                if (new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 1) <= maxDate) {
                    currentDate.setMonth(currentDate.getMonth() + 1);
                    renderCalendar();
                }
            });
            
            document.getElementById('calendar-month-dateN').addEventListener('change', (e) => {
                e.stopPropagation();
                currentDate.setMonth(parseInt(e.target.value));
                renderCalendar();
            });
            
            document.getElementById('calendar-year-dateN').addEventListener('change', (e) => {
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
            
            document.getElementById('calendar-month-dateN').addEventListener('click', (e) => e.stopPropagation());
            document.getElementById('calendar-year-dateN').addEventListener('click', (e) => e.stopPropagation());
            
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
    @endpush
</x-app-layout>

