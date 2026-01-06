<x-app-layout>
    <div class="container py-4">
		<div class="mb-3">
			<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-end gap-3">
                <ul class="nav nav-tabs border-0 presence-tabs flex-grow-1 w-100 w-md-auto">
                <li class="nav-item me-2 me-md-3">
                    <a class="nav-link active fw-bold text-warning activite-tab" href="#" data-activite="cantine" aria-current="page">{{ __('presence.cantine') }}</a>
                </li>
                <li class="nav-item me-2 me-md-3">
                    <a class="nav-link text-secondary activite-tab" href="#" data-activite="garderie_matin">{{ __('presence.garderie_matin') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-secondary activite-tab" href="#" data-activite="garderie_soir">{{ __('presence.garderie_soir') }}</a>
                </li>
				</ul>

                <div class="d-flex align-items-center gap-2 position-relative w-100 w-md-auto justify-content-between justify-content-md-start">
                    <div id="display-date" class="fw-semibold me-1 presence-date-text"></div>
                    <button id="open-date" type="button" class="btn btn-link p-0 presence-date-btn flex-shrink-0" aria-label="Choisir la date">
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <input id="presence-date" name="date" type="date" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" class="presence-date-input-hidden" />
                    <div id="custom-calendar" class="presence-calendar-dropdown"></div>
				</div>
			</div>
            <div class="presence-divider"></div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row gap-3 justify-content-between mb-3">
                    <div class="flex-grow-1">
                        <div class="small text-muted">{{ __('presence.classes') }}</div>
                        <div class="presence-class-picker">
                            <div class="position-relative">
                                <input id="classe-search" type="text" class="form-control presence-classe-search" placeholder="{{ __('presence.rechercher_classe') }}" autocomplete="off" />
                                <div id="classe-suggestions" class="presence-classe-suggestions"></div>
                            </div>
                            <div class="presence-class-actions">
                                <button id="select-all-classes" type="button" class="btn btn-outline-warning btn-sm presence-select-all-btn">
                                    {{ __('presence.selectionner_toutes') }}
                                </button>
                            </div>
                            <div id="selected-classes" class="presence-selected-classes"></div>
                        </div>
                    </div>
                    <div class="flex-grow-1 flex-lg-auto">
                        <div class="small text-muted">{{ __('presence.rechercher_eleve') }}</div>
                        <input id="search-student" type="text" class="form-control presence-search-input" placeholder="{{ __('presence.rechercher_eleve') }}" />
                    </div>
                </div>

                <div class="bg-light px-3 py-2 rounded mb-2 d-flex justify-content-between">
                    <div class="fw-semibold">{{ __('presence.eleve') }}</div>
                    <div class="fw-semibold text-center present-col">{{ __('presence.present') }}</div>
                </div>

                <div id="students-list"></div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <button id="save-presences" class="btn btn-warning fw-bold">{{ __('auth.enregistrer') }}</button>
                    <div class="d-flex align-items-center">
                        <label class="me-2 small text-muted">{{ __('presence.tout_selectionner') }}</label>
                        <div class="d-flex align-items-center justify-content-center present-col">
                            <input id="select-all" type="checkbox" class="checkbox-lg" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Conteneur pour les notifications -->
    <div id="notification-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;"></div>
</x-app-layout>

<script>
    (function() {
        const input = document.getElementById('presence-date');
        const out = document.getElementById('display-date');
        const btn = document.getElementById('open-date');
        const calendarDropdown = document.getElementById('custom-calendar');
        const studentsList = document.getElementById('students-list');
        const searchInput = document.getElementById('search-student');
        const activiteTabs = document.querySelectorAll('.activite-tab');
        const selectedClassesContainer = document.getElementById('selected-classes');
        const classeSearchInput = document.getElementById('classe-search');
        const classeSuggestions = document.getElementById('classe-suggestions');
        const classPicker = document.querySelector('.presence-class-picker');
        const selectAllClassesBtn = document.getElementById('select-all-classes');
        const texts = {
            selectClasses: @json(__('presence.selectionner_classes_hint')),
            noSelection: @json(__('presence.selectionner_classes_hint')),
            noResults: @json(__('presence.aucun_resultat')),
            removeClass: @json(__('presence.retirer_classe')),
            selectAllClasses: @json(__('presence.selectionner_toutes')),
            allClassesSelected: @json(__('presence.toutes_selectionnees')),
        };
        let allStudents = [];
        let allClasses = [];
        let selectedClasses = [];
        let currentDate = new Date(input.value);
        let calendarVisible = false;
        let currentActivite = 'cantine';
        let lastPresentSet = new Set();

        classeSearchInput.disabled = true;

        function formatDateToYYYYMMDD(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function formatFr(dateStr) {
            try {
                const d = new Date(dateStr + 'T00:00:00');
                const txt = d.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' });
                return txt.charAt(0).toUpperCase() + txt.slice(1);
            } catch (_) { return ''; }
        }
        function render() { out.textContent = formatFr(input.value); }

        function renderCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            const firstDay = new Date(year, month, 1);
            const startDate = new Date(firstDay);
            startDate.setDate(startDate.getDate() - startDate.getDay());
            
            const monthNames = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
            const dayNames = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
            
            const currentYear = today.getFullYear();
            const years = [];
            for (let y = currentYear; y >= currentYear - 10; y--) {
                years.push(y);
            }
            
            let html = `<div class="presence-calendar">
                <div class="presence-calendar-header">
                    <button type="button" class="presence-calendar-nav" id="prev-month"><i class="bi bi-chevron-left"></i></button>
                    <div class="presence-calendar-title-group">
                        <select id="calendar-month" class="presence-calendar-select">${monthNames.map((m, i) => `<option value="${i}" ${i === month ? 'selected' : ''}>${m}</option>`).join('')}</select>
                        <select id="calendar-year" class="presence-calendar-select">${years.map(y => `<option value="${y}" ${y === year ? 'selected' : ''}>${y}</option>`).join('')}</select>
                    </div>
                    <button type="button" class="presence-calendar-nav" id="next-month"><i class="bi bi-chevron-right"></i></button>
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
                const isFuture = current > today;
                const isSelected = dateStr === input.value;
                
                let classes = 'presence-calendar-day';
                if (!isCurrentMonth) classes += ' presence-calendar-day-other';
                if (isToday) classes += ' presence-calendar-day-today';
                if (isSelected) classes += ' presence-calendar-day-selected';
                if (isFuture) classes += ' presence-calendar-day-disabled';
                
                html += `<div class="${classes}" ${!isFuture ? `data-date="${dateStr}"` : ''}>${current.getDate()}</div>`;
                current.setDate(current.getDate() + 1);
            }
            
            html += `</div></div>`;
            calendarDropdown.innerHTML = html;
            
            document.getElementById('prev-month').addEventListener('click', (e) => {
                e.stopPropagation();
                currentDate.setMonth(currentDate.getMonth() - 1);
                renderCalendar();
            });
            
            document.getElementById('next-month').addEventListener('click', (e) => {
                e.stopPropagation();
                if (new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 1) <= today) {
                    currentDate.setMonth(currentDate.getMonth() + 1);
                    renderCalendar();
                }
            });
            
            document.getElementById('calendar-month').addEventListener('change', (e) => {
                e.stopPropagation();
                currentDate.setMonth(parseInt(e.target.value));
                renderCalendar();
            });
            
            document.getElementById('calendar-year').addEventListener('change', (e) => {
                e.stopPropagation();
                const newYear = parseInt(e.target.value);
                const newDate = new Date(newYear, currentDate.getMonth(), 1);
                if (newDate <= today) {
                    currentDate.setFullYear(newYear);
                    renderCalendar();
                } else {
                    e.target.value = currentDate.getFullYear();
                }
            });
            
            document.getElementById('calendar-month').addEventListener('click', (e) => e.stopPropagation());
            document.getElementById('calendar-year').addEventListener('click', (e) => e.stopPropagation());
            
            document.querySelectorAll('.presence-calendar-day[data-date]').forEach(day => {
                day.addEventListener('click', (e) => {
                    e.stopPropagation();
                    input.value = day.getAttribute('data-date');
                    render();
                    calendarDropdown.classList.remove('show');
                    calendarVisible = false;
                    loadStatus();
                });
            });
        }

        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            calendarVisible = !calendarVisible;
            if (calendarVisible) {
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
            if (classPicker && !classPicker.contains(e.target)) {
                hideSuggestions();
            }
        });

        input.addEventListener('change', () => {
            render();
            loadStatus();
        });
        render();

        async function fetchJson(url) {
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            return await res.json();
        }

        async function loadClasses() {
            allClasses = await fetchJson('{{ route('presence.classes') }}');
            if (allClasses.length) {
                selectedClasses = [allClasses[0]];
                classeSearchInput.disabled = false;
            }
            renderSelectedClasses();
            await loadStudents();
        }

        function updateSelectAllClassesButton() {
            if (!selectAllClassesBtn) return;
            if (!allClasses.length) {
                selectAllClassesBtn.disabled = true;
                selectAllClassesBtn.textContent = texts.selectAllClasses;
                return;
            }
            const allSelected = selectedClasses.length === allClasses.length && allClasses.length > 0;
            selectAllClassesBtn.disabled = allSelected;
            selectAllClassesBtn.textContent = allSelected ? texts.allClassesSelected : texts.selectAllClasses;
        }

        function renderSelectedClasses() {
            selectedClassesContainer.innerHTML = '';
            updateSelectAllClassesButton();
            if (selectedClasses.length === 0) {
                const placeholder = document.createElement('div');
                placeholder.className = 'text-muted small';
                placeholder.textContent = texts.selectClasses;
                selectedClassesContainer.appendChild(placeholder);
                return;
            }

            selectedClasses.forEach(cls => {
                const chip = document.createElement('div');
                chip.className = 'presence-class-chip';
                const span = document.createElement('span');
                span.textContent = cls.nom;
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'presence-class-chip-remove';
                btn.setAttribute('aria-label', `${texts.removeClass} ${cls.nom}`);
                btn.innerHTML = '&times;';
                btn.addEventListener('click', () => removeClass(cls.idClasse));
                chip.append(span, btn);
                selectedClassesContainer.appendChild(chip);
            });
        }

        function hideSuggestions() {
            classeSuggestions.classList.remove('show');
            classeSuggestions.innerHTML = '';
        }

        function renderSuggestions(showAll = false) {
            if (!allClasses.length) {
                hideSuggestions();
                return;
            }
            const query = normalizeText((classeSearchInput.value || '').trim());
            const available = allClasses.filter(c => !selectedClasses.some(sel => Number(sel.idClasse) === Number(c.idClasse)));
            let matches = available;
            if (query) {
                matches = available.filter(c => {
                    const nom = normalizeText(c.nom || '');
                    const niveau = normalizeText(c.niveau || '');
                    return nom.includes(query) || niveau.includes(query);
                });
            } else if (!showAll) {
                matches = available.slice(0, 5);
            }

            if (!matches.length) {
                hideSuggestions();
                return;
            }

            classeSuggestions.innerHTML = '';
            matches.slice(0, 8).forEach(cls => {
                const option = document.createElement('button');
                option.type = 'button';
                option.className = 'presence-classe-suggestion';
                option.textContent = cls.nom;
                option.addEventListener('click', () => addClassById(cls.idClasse));
                classeSuggestions.appendChild(option);
            });
            classeSuggestions.classList.add('show');
        }

        function addClassById(id) {
            const cls = allClasses.find(c => Number(c.idClasse) === Number(id));
            if (!cls || selectedClasses.some(item => Number(item.idClasse) === Number(cls.idClasse))) {
                hideSuggestions();
                return;
            }
            selectedClasses = [...selectedClasses, cls];
            classeSearchInput.value = '';
            hideSuggestions();
            renderSelectedClasses();
            loadStudents();
        }

        function removeClass(id) {
            selectedClasses = selectedClasses.filter(cls => Number(cls.idClasse) !== Number(id));
            renderSelectedClasses();
            loadStudents();
        }

        async function loadStudents() {
            if (!selectedClasses.length) {
                allStudents = [];
                filterAndRenderStudents();
                await loadStatus();
                return;
            }

            const params = new URLSearchParams();
            selectedClasses.forEach(cls => params.append('classe_ids[]', cls.idClasse));
            const students = await fetchJson(`{{ route('presence.students') }}?${params.toString()}`);
            allStudents = students;
            filterAndRenderStudents();
            await loadStatus();
        }

        function renderStudentsPlaceholder(message) {
            studentsList.innerHTML = `<div class="text-muted text-center py-4">${message}</div>`;
        }

        function filterAndRenderStudents() {
            const query = (searchInput.value || '').trim();
            const normalizedQuery = normalizeText(query);

            if (!selectedClasses.length) {
                renderStudentsPlaceholder(texts.noSelection);
                updateSelectAllState();
                return;
            }

            const filtered = normalizedQuery
                ? allStudents.filter(s => {
                    const prenomNorm = normalizeText(s.prenom || '');
                    const nomNorm = normalizeText(s.nom || '');
                    const fullNorm = normalizeText(`${s.prenom} ${s.nom}`);
                    const classeNorm = normalizeText(s.classe_nom || '');
                    return prenomNorm.includes(normalizedQuery) ||
                        nomNorm.includes(normalizedQuery) ||
                        fullNorm.includes(normalizedQuery) ||
                        classeNorm.includes(normalizedQuery);
                })
                : allStudents;

            if (!filtered.length) {
                renderStudentsPlaceholder(texts.noResults);
                updateSelectAllState();
                return;
            }

            studentsList.innerHTML = '';
            for (const s of filtered) {
                const row = document.createElement('div');
                row.className = 'd-flex align-items-center justify-content-between py-2 border-bottom student-row';
                row.setAttribute('data-eleve-id', s.idEnfant);
                const initial = (s.prenom || s.nom || 'U').toString().charAt(0).toUpperCase();
                const classeInfo = s.classe_nom ? ` <span class="text-muted small ms-2">(${s.classe_nom})</span>` : '';
                row.innerHTML = `<div class="d-flex align-items-center">
                        <div class="avatar-circle me-3">
                            <span class="text-dark">${initial}</span>
                        </div>
                        <div>${s.prenom} ${s.nom}${classeInfo}</div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center present-col">
                        <input class="presence-checkbox checkbox-lg" type="checkbox" data-eleve-id="${s.idEnfant}" />
                    </div>`;
                studentsList.appendChild(row);
            }
            attachCheckboxListeners();
            applyStatusToCheckboxes();
        }

        function normalizeText(text) {
            return text.toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '');
        }

        searchInput.addEventListener('input', filterAndRenderStudents);
        classeSearchInput.addEventListener('input', () => renderSuggestions(true));
        classeSearchInput.addEventListener('focus', () => renderSuggestions(true));
        selectAllClassesBtn?.addEventListener('click', () => {
            if (!allClasses.length) {
                return;
            }
            selectedClasses = [...allClasses];
            classeSearchInput.value = '';
            hideSuggestions();
            renderSelectedClasses();
            loadStudents();
        });

        const selectAll = document.getElementById('select-all');
        function getAllCheckboxes() { return Array.from(document.querySelectorAll('.presence-checkbox')); }
        function updateSelectAllState() {
            const boxes = getAllCheckboxes();
            if (boxes.length === 0) { selectAll.checked = false; return; }
            selectAll.checked = boxes.every(cb => cb.checked);
        }
        function attachCheckboxListeners() { getAllCheckboxes().forEach(cb => cb.addEventListener('change', updateSelectAllState)); }
        selectAll.addEventListener('change', function() {
            getAllCheckboxes().forEach(cb => cb.checked = selectAll.checked);
            updateSelectAllState();
        });

        function applyStatusToCheckboxes() {
            getAllCheckboxes().forEach(cb => {
                const id = parseInt(cb.getAttribute('data-eleve-id'));
                cb.checked = lastPresentSet.has(id);
            });
            updateSelectAllState();
        }

        async function loadStatus() {
            const date = input.value;
            if (!date || !selectedClasses.length) {
                lastPresentSet = new Set();
                applyStatusToCheckboxes();
                return;
            }
            const params = new URLSearchParams();
            selectedClasses.forEach(cls => params.append('classe_ids[]', cls.idClasse));
            params.append('date', date);
            params.append('activite', currentActivite);
            const res = await fetch(`{{ route('presence.status') }}?${params.toString()}`, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            lastPresentSet = new Set((data.presentIds || []).map(Number));
            applyStatusToCheckboxes();
        }

        activiteTabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                const activite = this.getAttribute('data-activite');
                if (activite === currentActivite) return;
                
                activiteTabs.forEach(t => {
                    t.classList.remove('active', 'fw-bold', 'text-warning');
                    t.classList.add('text-secondary');
                });
                this.classList.add('active', 'fw-bold', 'text-warning');
                this.classList.remove('text-secondary');
                
                currentActivite = activite;
                loadStatus();
            });
        });

        function showSuccessNotification() {
            const container = document.getElementById('notification-container');
            const notification = document.createElement('div');
            notification.className = 'alert alert-success alert-dismissible fade show shadow';
            notification.style.minWidth = '300px';
            notification.setAttribute('role', 'alert');
            notification.innerHTML = `
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong>Succès !</strong> Les présences ont été enregistrées avec succès.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            container.innerHTML = '';
            container.appendChild(notification);
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.classList.remove('show');
                    setTimeout(() => notification.remove(), 300);
                }
            }, 4000);
        }

        const saveBtn = document.getElementById('save-presences');
        saveBtn.addEventListener('click', async function() {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const items = getAllCheckboxes().map(cb => ({ idEnfant: parseInt(cb.getAttribute('data-eleve-id')), present: cb.checked }));
            const payload = { date: input.value, activite: currentActivite, items };
            
            saveBtn.disabled = true;
            const originalText = saveBtn.textContent;
            saveBtn.textContent = 'Enregistrement...';
            
            try {
                const res = await fetch(`{{ route('presence.save') }}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                    body: JSON.stringify(payload)
                });
                
                if (res.ok) {
                    showSuccessNotification();
                } else {
                    console.error('Erreur lors de l\'enregistrement');
                }
            } catch (error) {
                console.error('Erreur:', error);
            } finally {
                setTimeout(() => {
                    saveBtn.disabled = false;
                    saveBtn.textContent = originalText;
                }, 600);
            }
        });

        renderSelectedClasses();
        loadClasses();
    })();
</script>


