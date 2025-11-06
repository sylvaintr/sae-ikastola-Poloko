<x-app-layout>
    <div class="container py-4">
		<div class="mb-3">
			<div class="d-flex justify-content-between align-items-end">
                <ul class="nav nav-tabs border-0 presence-tabs">
                <li class="nav-item me-3">
                    <a class="nav-link active fw-bold text-warning activite-tab" href="#" data-activite="cantine" aria-current="page">{{ __('presence.cantine') }}</a>
                </li>
                <li class="nav-item me-3">
                    <a class="nav-link text-secondary activite-tab" href="#" data-activite="garderie_matin">{{ __('presence.garderie_matin') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-secondary activite-tab" href="#" data-activite="garderie_soir">{{ __('presence.garderie_soir') }}</a>
                </li>
				</ul>

                <div class="d-flex align-items-center gap-2 position-relative">
                    <div id="display-date" class="fw-semibold me-1 presence-date-text"></div>
                    <button id="open-date" type="button" class="btn btn-link p-0 presence-date-btn" aria-label="Choisir la date">
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
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <div class="small text-muted">{{ __('presence.classe') }}</div>
                        <select id="classe-select" class="form-select presence-classe-select"></select>
                    </div>
                    <div>
                        <div class="small text-muted">&nbsp;</div>
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
        const classeSelect = document.getElementById('classe-select');
        const studentsList = document.getElementById('students-list');
        const searchInput = document.getElementById('search-student');
        const activiteTabs = document.querySelectorAll('.activite-tab');
        let allStudents = [];
        let currentDate = new Date(input.value);
        let calendarVisible = false;
        let currentActivite = 'cantine';

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
            const lastDay = new Date(year, month + 1, 0);
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
        });

        input.addEventListener('change', render);
        render();

        
        async function fetchJson(url) {
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            return await res.json();
        }

        async function loadClasses() {
            const classes = await fetchJson('{{ route('presence.classes') }}');
            classeSelect.innerHTML = '';
            for (const c of classes) {
                const opt = document.createElement('option');
                opt.value = c.idClasse;
                opt.textContent = `${c.nom}`.trim();
                classeSelect.appendChild(opt);
            }
            if (classes.length) {
                await loadStudents(classes[0].idClasse);
            }
        }

        async function loadStudents(classeId) {
            const students = await fetchJson('{{ route('presence.students') }}' + `?classe_id=${classeId}`);
            allStudents = students;
            filterAndRenderStudents();
        }

        function filterAndRenderStudents() {
            const query = (searchInput.value || '').trim();
            const normalizedQuery = normalizeText(query);
            const filtered = query 
                ? allStudents.filter(s => {
                    const prenomNorm = normalizeText(s.prenom || '');
                    const nomNorm = normalizeText(s.nom || '');
                    const fullNorm = normalizeText(`${s.prenom} ${s.nom}`);
                    return prenomNorm.includes(normalizedQuery) || 
                           nomNorm.includes(normalizedQuery) ||
                           fullNorm.includes(normalizedQuery);
                })
                : allStudents;

            studentsList.innerHTML = '';
            for (const s of filtered) {
                const row = document.createElement('div');
                row.className = 'd-flex align-items-center justify-content-between py-2 border-bottom student-row';
                row.setAttribute('data-eleve-id', s.idEnfant);
                row.innerHTML = `<div class="d-flex align-items-center">
                        <div class="avatar-circle me-3">
                            <span class="text-dark">${(s.prenom || s.nom || 'U').toString().charAt(0).toUpperCase()}</span>
                        </div>
                        <div>${s.prenom} ${s.nom}</div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center present-col"><input class="presence-checkbox checkbox-lg" type="checkbox" data-eleve-id="${s.idEnfant}" /></div>`;
                studentsList.appendChild(row);
            }
            updateSelectAllState();
            attachCheckboxListeners();
            // Recharger le statut après filtrage pour maintenir les cases cochées
            loadStatus();
        }

        function normalizeText(text) {
            return text.toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '');
        }

        searchInput.addEventListener('input', filterAndRenderStudents);

        classeSelect?.addEventListener('change', async function() {
            const v = this.value;
            searchInput.value = '';
            if (v) { await loadStudents(v); }
        });

        const selectAll = document.getElementById('select-all');
        function getAllCheckboxes() { return Array.from(document.querySelectorAll('.presence-checkbox')); }
        function updateSelectAllState() {
            const boxes = getAllCheckboxes();
            if (boxes.length === 0) { selectAll.checked = false; return; }
            selectAll.checked = boxes.every(cb => cb.checked);
        }
        function attachCheckboxListeners() { getAllCheckboxes().forEach(cb => cb.addEventListener('change', updateSelectAllState)); }
        selectAll.addEventListener('change', function() { getAllCheckboxes().forEach(cb => cb.checked = selectAll.checked); });

        async function loadStatus() {
            const classeId = classeSelect.value;
            const date = input.value;
            if (!classeId || !date) return;
            const res = await fetch(`{{ route('presence.status') }}?classe_id=${classeId}&date=${date}&activite=${currentActivite}`, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            const presentSet = new Set(data.presentIds || []);
            getAllCheckboxes().forEach(cb => {
                const id = parseInt(cb.getAttribute('data-eleve-id'));
                cb.checked = presentSet.has(id);
            });
            updateSelectAllState();
        }

        // Recharger le statut quand la date change
        input.addEventListener('change', loadStatus);

        // Gestion des onglets d'activité
        activiteTabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                const activite = this.getAttribute('data-activite');
                if (activite === currentActivite) return;
                
                // Mettre à jour l'onglet actif
                activiteTabs.forEach(t => {
                    t.classList.remove('active', 'fw-bold', 'text-warning');
                    t.classList.add('text-secondary');
                });
                this.classList.add('active', 'fw-bold', 'text-warning');
                this.classList.remove('text-secondary');
                
                // Changer l'activité courante
                currentActivite = activite;
                
                // Recharger les données pour la nouvelle activité
                loadStatus();
            });
        });

        // Fonction pour afficher la notification de succès
        function showSuccessNotification() {
            const container = document.getElementById('notification-container');
            
            // Créer l'élément de notification
            const notification = document.createElement('div');
            notification.className = 'alert alert-success alert-dismissible fade show shadow';
            notification.style.minWidth = '300px';
            notification.setAttribute('role', 'alert');
            notification.innerHTML = `
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong>Succès !</strong> Les présences ont été enregistrées avec succès.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            
            // Ajouter au conteneur
            container.innerHTML = '';
            container.appendChild(notification);
            
            // Supprimer automatiquement après 4 secondes
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.classList.remove('show');
                    setTimeout(() => {
                        if (notification.parentNode) {
                            notification.remove();
                        }
                    }, 300);
                }
            }, 4000);
        }

        // Enregistrer
        const saveBtn = document.getElementById('save-presences');
        saveBtn.addEventListener('click', async function() {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const items = getAllCheckboxes().map(cb => ({ idEnfant: parseInt(cb.getAttribute('data-eleve-id')), present: cb.checked }));
            const payload = { date: input.value, activite: currentActivite, items };
            
            // Désactiver le bouton pendant l'envoi
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
                    // Afficher la notification de succès
                    showSuccessNotification();
                } else {
                    // En cas d'erreur, on pourrait afficher une notification d'erreur
                    console.error('Erreur lors de l\'enregistrement');
                }
            } catch (error) {
                console.error('Erreur:', error);
            } finally {
                // Réactiver le bouton après un court délai
                setTimeout(() => {
                    saveBtn.disabled = false;
                    saveBtn.textContent = originalText;
                }, 600);
            }
        });

        // init
        loadClasses().then(loadStatus);
    })();
</script>


