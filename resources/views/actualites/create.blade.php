<x-app-layout>
    @vite(['resources/js/actualite.js'])

    {{-- STYLE CSS DU CALENDRIER PERSONNALISÉ --}}
    <style>
        /* Conteneur principal du calendrier (caché par défaut) */
        .datepicker-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            width: 320px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 20px;
            z-index: 1000;
            display: none;
            /* Caché par défaut */
            margin-top: 5px;
            border: 1px solid #eee;
        }

        .datepicker-dropdown.active {
            display: block;
        }

        /* En-tête : Flèches et Dropdowns */
        .datepicker-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .datepicker-nav-btn {
            background: none;
            border: none;
            color: #e69a2d;
            /* Orange */
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0 10px;
        }

        .datepicker-selectors {
            display: flex;
            gap: 5px;
        }

        .datepicker-select {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 2px 5px;
            font-size: 0.9rem;
            color: #333;
            cursor: pointer;
            outline: none;
        }

        /* Grille des jours */
        .datepicker-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            row-gap: 10px;
        }

        /* Jours de la semaine (Lun, Mar...) */
        .day-name {
            font-weight: bold;
            color: #555;
            font-size: 0.85rem;
            margin-bottom: 10px;
        }

        /* Numéros des jours */
        .day-number {
            width: 35px;
            height: 35px;
            line-height: 35px;
            margin: 0 auto;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.95rem;
            color: #333;
        }

        .day-number:hover:not(.empty) {
            background-color: #f0f0f0;
        }

        /* Jour sélectionné (Orange) */
        .day-number.selected {
            background-color: #e69a2d;
            color: white;
            font-weight: bold;
        }

        /* Jours du mois précédent/suivant (gris) */
        .day-number.faded {
            color: #ccc;
        }
    </style>

    <div class="container py-5">
        <h2 class="mb-4 fw-bold text-center">{{ __('actualite.nouvelle_actualite') }}</h2>

        <form id="actuForm" action="{{ route('admin.actualites.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- ... LE DÉBUT DE VOTRE FORMULAIRE (Code inchangé pour les onglets FR/EU) ... --}}
            <div class="row">
                <div class="col-lg-8">
                    {{-- Je garde votre code existant pour la colonne de gauche --}}
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white p-0">
                            <ul class="nav nav-tabs card-header-tabs m-0" id="langTab">
                                <li class="nav-item">
                                    <button class="nav-link active fw-bold" id="tab-fr-btn" data-bs-toggle="tab"
                                        data-bs-target="#fr" type="button">
                                        {{ __('nav.francais') }}
                                        <span class="error-icon text-danger ms-2" style="display:none;"><i
                                                class="bi bi-exclamation-circle-fill"></i></span>
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link fw-bold" id="tab-eus-btn" data-bs-toggle="tab"
                                        data-bs-target="#eus" type="button">
                                        {{ __('nav.basque') }}
                                        <span class="error-icon text-danger ms-2" style="display:none;"><i
                                                class="bi bi-exclamation-circle-fill"></i></span>
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="fr">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('actualite.titre') }} (FR) <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="titrefr" class="form-control" maxlength="30"
                                            required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('actualite.description') }} (FR) <span
                                                class="text-danger">*</span></label>
                                        <textarea name="descriptionfr" class="form-control" maxlength="100" rows="2" required></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('actualite.contenu') }} (FR) <span
                                                class="text-danger">*</span></label>
                                        <textarea id="contenufr" name="contenufr" class="form-control mb-3" rows="6" required></textarea>
                                        <div id="renducontenufr"></div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="eus">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('actualite.titre') }} (EUS) <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="titreeus" class="form-control" maxlength="30"
                                            required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('actualite.description') }} (EUS) <span
                                                class="text-danger">*</span></label>
                                        <textarea name="descriptioneus" class="form-control" maxlength="100" rows="2" required></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('actualite.contenu') }} (EUS) <span
                                                class="text-danger">*</span></label>
                                        <textarea id="contenueus" name="contenueus" class="form-control" rows="6" required></textarea>
                                        <div id="renducontenueus"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- COLONNE DROITE : PARAMÈTRES --}}
                <div class="col-lg-4">
                    <div class="card shadow-sm bg-light border-0">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-3">{{ __('actualite.parametres') }}</h5>

                            <div class="mb-3">
                                <label for="type" class="form-label fw-bold">{{ __('actualite.type') }}</label>
                                <select id="type" name="type" class="form-select">
                                    <option value="public">{{ __('actualite.public') }}</option>
                                    <option value="private">{{ __('actualite.prive') }}</option>
                                </select>
                            </div>


                            <div class="mb-3 position-relative" id="custom-datepicker-wrapper">
                                <label for="dateP" class="form-label fw-bold">{{ __('actualite.date_publication') }}
                                    <span class="text-danger">*</span></label>


                                <input type="text" id="dateP" name="dateP"
                                    class="form-control bg-white cursor-pointer" value="{{ date('d/m/Y') }}" readonly
                                    required placeholder="Sélectionner une date" onclick="toggleCalendar()">

                                <div id="calendar-popup" class="datepicker-dropdown">

                                    <div class="datepicker-header">
                                        <button type="button" class="datepicker-nav-btn"
                                            onclick="changeMonth(-1)">&lt;</button>
                                        <div class="datepicker-selectors">
                                            <select id="monthSelect" class="datepicker-select"
                                                onchange="jumpToDate()"></select>
                                            <select id="yearSelect" class="datepicker-select"
                                                onchange="jumpToDate()"></select>
                                        </div>
                                        <button type="button" class="datepicker-nav-btn"
                                            onclick="changeMonth(1)">&gt;</button>
                                    </div>


                                    <div class="datepicker-grid" id="calendar-grid">

                                    </div>
                                </div>
                            </div>



                            <div class="mb-3">
                                <label for="lien"
                                    class="form-label fw-bold">{{ __('actualite.lien_externe') }}</label>
                                <input type="url" id="lien" name="lien" class="form-control"
                                    placeholder="https://...">
                            </div>

                            {{-- ETIQUETTES (Code précédent conservé) --}}
                            <div class="mb-3 position-relative">
                                <label class="form-label fw-bold">{{ __('actualite.etiquettes') }}</label>
                                <select name="etiquettes[]" id="real-tag-select" multiple class="d-none">
                                    @foreach ($etiquettes as $tag)
                                        <option value="{{ $tag->idEtiquette }}">{{ $tag->nom }}</option>
                                    @endforeach
                                </select>
                                <div class="form-control p-2" style="min-height: 45px;">
                                    <div id="selected-tags-container" class="d-flex flex-wrap gap-1 mb-1"></div>
                                    <input type="text" id="tag-search-input" class="border-0 w-100 p-0"
                                        style="outline: none;" placeholder="Rechercher...">
                                </div>
                                <div id="tag-suggestions" class="list-group position-absolute w-100 shadow mt-1"
                                    style="display: none; z-index: 1000;"></div>
                            </div>

                            {{-- IMAGES (Code précédent conservé) --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold">{{ __('actualite.images') }}</label>
                                <input type="file" id="images" name="images[]" class="form-control mb-2"
                                    multiple accept="image/*">
                                <div id="image-preview-container" class="row g-2 bg-white p-2 rounded border"
                                    style="display:none;"></div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" id="submitBtn" class="btn btn-warning py-2"
                                    disabled>{{ __('actualite.publier') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- MODAL ZOOM (Code précédent conservé) --}}
    <div class="modal fade" id="imageZoomModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body p-0 text-center position-relative">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                        data-bs-dismiss="modal"></button>
                    <img id="modalImageFull" src="" class="img-fluid rounded shadow"
                        style="max-height: 90vh;">
                </div>
            </div>
        </div>
    </div>
    @php
        $monthNames = [
            __('actualite.janvier'),
            __('actualite.fevrier'),
            __('actualite.mars'),
            __('actualite.avril'),
            __('actualite.mai'),
            __('actualite.juin'),
            __('actualite.juillet'),
            __('actualite.aout'),
            __('actualite.septembre'),
            __('actualite.octobre'),
            __('actualite.novembre'),
            __('actualite.decembre'),
        ];

        $dayNames = [
            __('actualite.dim'),
            __('actualite.lun'),
            __('actualite.mar'),
            __('actualite.mer'),
            __('actualite.jeu'),
            __('actualite.ven'),
            __('actualite.sam'),
        ];
    @endphp


<script>
        document.addEventListener('DOMContentLoaded', function() {

            // ==========================================
            // 1. DATEPICKER CUSTOM (Format JJ/MM/AAAA)
            // ==========================================
            const dateInput = document.getElementById('dateP');
            const calendarPopup = document.getElementById('calendar-popup');
            const grid = document.getElementById('calendar-grid');
            const monthSelect = document.getElementById('monthSelect');
            const yearSelect = document.getElementById('yearSelect');

            // Récupération des données traduites depuis Blade
            const monthNames = @json($monthNames);
            const dayNames = @json($dayNames);

            // --- FONCTION UTILITAIRE : Convertir "JJ/MM/AAAA" en objet Date JS ---
            function parseDateFR(dateStr) {
                if (!dateStr) return new Date();
                const parts = dateStr.split('/');
                if (parts.length === 3) {
                    // new Date(Année, Mois (0-11), Jour)
                    return new Date(parts[2], parts[1] - 1, parts[0]);
                }
                return new Date(); // Fallback date actuelle
            }

            // Variables d'état
            let currentDate = new Date(); // Date visible (navigation)
            let selectedDate = new Date(); // Date cliquée

            // Initialisation avec la valeur de l'input
            if (dateInput.value) {
                const parsed = parseDateFR(dateInput.value);
                selectedDate = parsed;
                currentDate = new Date(parsed); // Clone pour la navigation
            }

            // --- DÉFINITION DES FONCTIONS ---

            function renderCalendar() {
                grid.innerHTML = "";

                // Synchro Dropdowns
                monthSelect.value = currentDate.getMonth();
                yearSelect.value = currentDate.getFullYear();

                // Entête Jours
                dayNames.forEach(name => {
                    const div = document.createElement('div');
                    div.className = 'day-name';
                    div.innerText = name;
                    grid.appendChild(div);
                });

                const year = currentDate.getFullYear();
                const month = currentDate.getMonth();

                const firstDay = new Date(year, month, 1).getDay(); // 0 = Dimanche
                const daysInMonth = new Date(year, month + 1, 0).getDate();
                const daysInPrevMonth = new Date(year, month, 0).getDate();

                // Jours mois précédent
                for (let i = 0; i < firstDay; i++) {
                    const div = document.createElement('div');
                    div.className = 'day-number faded';
                    div.innerText = daysInPrevMonth - firstDay + 1 + i;
                    grid.appendChild(div);
                }

                // Jours mois actuel
                for (let i = 1; i <= daysInMonth; i++) {
                    const div = document.createElement('div');
                    div.className = 'day-number';
                    div.innerText = i;

                    // Comparaison stricte pour la surbrillance
                    if (i === selectedDate.getDate() &&
                        month === selectedDate.getMonth() &&
                        year === selectedDate.getFullYear()) {
                        div.classList.add('selected');
                    }

                    // Clic sur un jour
                    div.onclick = function() {
                        selectedDate = new Date(year, month, i);
                        
                        // --- MODIFICATION ICI : FORMATAGE FR ---
                        const yearStr = selectedDate.getFullYear();
                        const monthStr = String(selectedDate.getMonth() + 1).padStart(2, '0');
                        const dayStr = String(selectedDate.getDate()).padStart(2, '0');

                        // Format : JJ/MM/AAAA
                        const formattedDate = `${dayStr}/${monthStr}/${yearStr}`;

                        dateInput.value = formattedDate;

                        dateInput.dispatchEvent(new Event('input'));
                        dateInput.dispatchEvent(new Event('change'));

                        renderCalendar();
                        calendarPopup.classList.remove('active');
                    };

                    grid.appendChild(div);
                }
            }

            // --- EXPOSITION GLOBALE (WINDOW) ---

            window.toggleCalendar = function() {
                calendarPopup.classList.toggle('active');
                if (calendarPopup.classList.contains('active')) {
                    // Recalibrer si l'input a changé manuellement ou via backend
                    if (dateInput.value) {
                        const parsed = parseDateFR(dateInput.value);
                        // On met à jour la vue sur la date de l'input
                        currentDate = new Date(parsed); 
                        selectedDate = parsed;
                    }
                    renderCalendar();
                }
            };

            window.changeMonth = function(offset) {
                currentDate.setMonth(currentDate.getMonth() + offset);
                renderCalendar();
            };

            window.jumpToDate = function() {
                currentDate.setMonth(parseInt(monthSelect.value));
                currentDate.setFullYear(parseInt(yearSelect.value));
                renderCalendar();
            };

            // --- INITIALISATION ---

            function initCalendar() {
                const currentYear = new Date().getFullYear();
                for (let i = currentYear - 5; i <= currentYear + 5; i++) {
                    let opt = document.createElement('option');
                    opt.value = i;
                    opt.text = i;
                    yearSelect.appendChild(opt);
                }

                monthNames.forEach((name, index) => {
                    let opt = document.createElement('option');
                    opt.value = index;
                    opt.text = name;
                    monthSelect.appendChild(opt);
                });

                renderCalendar();
            }

            initCalendar();

            // Fermeture au clic dehors
            document.addEventListener('click', function(e) {
                const wrapper = document.getElementById('custom-datepicker-wrapper');
                if (wrapper && !wrapper.contains(e.target)) {
                    calendarPopup.classList.remove('active');
                }
            });


            // ==========================================
            // 2. ETIQUETTES (Code Inchangé)
            // ==========================================
            const realTagSelect = document.getElementById('real-tag-select');
            const tagSearchInput = document.getElementById('tag-search-input');
            const tagSuggestions = document.getElementById('tag-suggestions');
            const tagContainer = document.getElementById('selected-tags-container');

            const allTags = Array.from(realTagSelect.options).map(opt => ({
                id: opt.value, name: opt.text, selected: opt.selected
            }));

            function renderTags() {
                tagContainer.innerHTML = '';
                allTags.forEach(tag => {
                    if (tag.selected) {
                        const badge = document.createElement('span');
                        badge.className = 'badge bg-warning text-dark d-flex align-items-center';
                        badge.innerHTML = `${tag.name} <i class="bi bi-x ms-1 cursor-pointer" onclick="toggleTag('${tag.id}')"></i>`;
                        tagContainer.appendChild(badge);
                    }
                });
                Array.from(realTagSelect.options).forEach(opt => {
                    opt.selected = allTags.find(t => t.id === opt.value).selected;
                });
            }

            window.toggleTag = function(id) {
                const tag = allTags.find(t => t.id === id);
                if (tag) { tag.selected = !tag.selected; renderTags(); tagSearchInput.value = ''; tagSuggestions.style.display = 'none'; }
            };

            tagSearchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase();
                tagSuggestions.innerHTML = '';
                if (query.length === 0) { tagSuggestions.style.display = 'none'; return; }
                const filtered = allTags.filter(t => t.name.toLowerCase().includes(query) && !t.selected);
                if (filtered.length > 0) {
                    tagSuggestions.style.display = 'block';
                    filtered.forEach(tag => {
                        const btn = document.createElement('button');
                        btn.type = 'button'; btn.className = 'list-group-item list-group-item-action';
                        btn.innerText = tag.name;
                        btn.onclick = () => { toggleTag(tag.id); tagSearchInput.focus(); };
                        tagSuggestions.appendChild(btn);
                    });
                } else { tagSuggestions.style.display = 'none'; }
            });

            document.addEventListener('click', e => {
                if (!tagSearchInput.contains(e.target) && !tagSuggestions.contains(e.target)) tagSuggestions.style.display = 'none';
            });


            // ==========================================
            // 3. IMAGES (Code Inchangé)
            // ==========================================
            const imageInput = document.getElementById('images');
            const previewContainer = document.getElementById('image-preview-container');
            const zoomModal = new bootstrap.Modal(document.getElementById('imageZoomModal'));
            const fullImage = document.getElementById('modalImageFull');

            imageInput.addEventListener('change', function() {
                previewContainer.innerHTML = '';
                if (this.files.length > 0) { previewContainer.style.display = 'flex'; previewContainer.style.flexWrap = 'wrap'; } 
                else { previewContainer.style.display = 'none'; return; }

                Array.from(this.files).forEach(file => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = e => {
                            const col = document.createElement('div'); col.className = 'col-3 position-relative';
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.className = 'img-thumbnail w-100 shadow-sm';
                            img.style.height = '80px'; img.style.objectFit = 'cover'; img.style.cursor = 'zoom-in';
                            img.onclick = () => { fullImage.src = e.target.result; zoomModal.show(); };
                            col.appendChild(img); previewContainer.appendChild(col);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            });


            // ==========================================
            // 4. VALIDATION (Code Inchangé)
            // ==========================================
            const form = document.getElementById('actuForm');
            const submitBtn = document.getElementById('submitBtn');
            const tabFrBtn = document.getElementById('tab-fr-btn');
            const tabEusBtn = document.getElementById('tab-eus-btn');
            const errFr = tabFrBtn.querySelector('.error-icon');
            const errEus = tabEusBtn.querySelector('.error-icon');

            function validateForm() {
                const formValid = form.checkValidity();
                submitBtn.disabled = !formValid;

                let frValid = true;
                document.querySelectorAll('#fr [required]').forEach(i => { if (!i.checkValidity()) frValid = false; });
                errFr.style.display = frValid ? 'none' : 'inline';
                frValid ? tabFrBtn.classList.remove('text-danger') : tabFrBtn.classList.add('text-danger');

                let eusValid = true;
                document.querySelectorAll('#eus [required]').forEach(i => { if (!i.checkValidity()) eusValid = false; });
                errEus.style.display = eusValid ? 'none' : 'inline';
                eusValid ? tabEusBtn.classList.remove('text-danger') : tabEusBtn.classList.add('text-danger');
            }

            form.addEventListener('input', validateForm);
            form.addEventListener('change', validateForm);
            validateForm();

            if (typeof AfficherMarkdownfromBalise === "function") {
                document.getElementById('contenufr').addEventListener('input', () => AfficherMarkdownfromBalise('contenufr', 'renducontenufr'));
                document.getElementById('contenueus').addEventListener('input', () => AfficherMarkdownfromBalise('contenueus', 'renducontenueus'));
            }
        });
    </script>
</x-app-layout>
