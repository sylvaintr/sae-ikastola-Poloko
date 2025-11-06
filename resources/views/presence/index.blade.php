<x-app-layout>
    <div class="container py-4">
		<div class="mb-3">
			<div class="d-flex justify-content-between align-items-end">
                <ul class="nav nav-tabs border-0 presence-tabs">
                <li class="nav-item me-3">
                    <a class="nav-link active fw-bold text-warning" href="#" aria-current="page">{{ __('presence.cantine') }}</a>
                </li>
                <li class="nav-item me-3">
                    <a class="nav-link text-secondary" href="#">{{ __('presence.garderie_matin') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-secondary" href="#">{{ __('presence.garderie_soir') }}</a>
                </li>
				</ul>

                <div class="d-flex align-items-center gap-2">
                    <div id="display-date" class="fw-semibold me-1 presence-date-text"></div>
                    <button id="open-date" type="button" class="btn btn-link p-0 presence-date-btn" aria-label="Choisir la date">
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <input id="presence-date" name="date" type="date" value="{{ now()->toDateString() }}" class="presence-date-input-hidden" />
				</div>
			</div>
            <div class="presence-divider"></div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="me-3">
                        <div class="small text-muted">{{ __('presence.classe') }}</div>
                        <select id="classe-select" class="form-select presence-classe-select"></select>
                    </div>
                </div>

                <div class="bg-light px-3 py-2 rounded mb-2 d-flex justify-content-between">
                    <div class="fw-semibold">{{ __('presence.eleve') }}</div>
                    <div class="fw-semibold text-center present-col">{{ __('presence.present') }}</div>
                </div>

                <div id="students-list"></div>

                <div class="d-flex justify-content-end align-items-center mt-3">
                    <label class="me-2 small text-muted">{{ __('presence.tout_selectionner') }}</label>
                    <div class="d-flex align-items-center justify-content-center present-col">
                        <input id="select-all" type="checkbox" class="checkbox-lg" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    (function() {
        const input = document.getElementById('presence-date');
        const out = document.getElementById('display-date');
        const btn = document.getElementById('open-date');
        const classeSelect = document.getElementById('classe-select');
        const studentsList = document.getElementById('students-list');
        function formatFr(dateStr) {
            try {
                const d = new Date(dateStr);
                const txt = d.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' });
                return txt.charAt(0).toUpperCase() + txt.slice(1);
            } catch (_) { return ''; }
        }
        function render() { out.textContent = formatFr(input.value); }
        input.addEventListener('change', render);
        btn.addEventListener('click', function() {
            if (typeof input.showPicker === 'function') {
                input.showPicker();
            } else {
                input.click();
            }
        });
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
            studentsList.innerHTML = '';
            for (const s of students) {
                const row = document.createElement('div');
                row.className = 'd-flex align-items-center justify-content-between py-2 border-bottom';
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
        }

        classeSelect?.addEventListener('change', async function() {
            const v = this.value;
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

        loadClasses();
    })();
</script>


