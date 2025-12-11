<x-app-layout>
    @vite(['resources/js/actualite.js'])

    {{-- STYLE CSS --}}
    <style>
        /* Styles Calendrier (inchangés) */
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
            margin-top: 5px;
            border: 1px solid #eee;
        }

        .datepicker-dropdown.active {
            display: block;
        }

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

        .datepicker-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            row-gap: 10px;
        }

        .day-name {
            font-weight: bold;
            color: #555;
            font-size: 0.85rem;
            margin-bottom: 10px;
        }

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

        .day-number.selected {
            background-color: #e69a2d;
            color: white;
            font-weight: bold;
        }

        .day-number.faded {
            color: #ccc;
        }

        /* Styles Images */
        .existing-image-container {
            position: relative;
            display: inline-block;
            margin: 5px;
            transition: transform 0.2s;
        }

        .existing-image-container:hover {
            transform: scale(1.05);
        }

        .clickable-image {
            cursor: zoom-in;
            transition: opacity 0.2s;
        }

        .clickable-image:hover {
            opacity: 0.9;
        }

        .btn-delete-image {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            font-size: 14px;
            border: 2px solid white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            z-index: 10;
        }

        .btn-delete-image:hover {
            background: #bb2d3b;
            transform: scale(1.1);
        }
    </style>

    <div class="container py-5">
        <h2 class="mb-4 fw-bold text-center">{{ Lang::get('actualite.modifier_actualite', [], 'eus') }}
            @if (Lang::getLocale() == 'fr')
                <p class="fw-light mb-0">{{ __('actualite.modifier_actualite') }}</p>
            @endif
        </h2>

        <form id="actuForm" action="{{ route('admin.actualites.update', $actualite->idActualite) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">
                {{-- COLONNE GAUCHE (FR/EU) --}}
                <div class="col-lg-8">
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
                                {{-- FR --}}
                                <div class="tab-pane fade show active" id="fr">
                                    <div class="mb-3">
                                        <label for="titrefr" class="form-label">{{ __('actualite.titre') }} (FR) <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="titrefr" id="titrefr" class="form-control @error('titrefr') is-invalid @enderror" maxlength="30"
                                            value="{{ old('titrefr', $actualite->titrefr) }}" required>
                                        @error('titrefr')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="descriptionfr" class="form-label">{{ __('actualite.description') }} (FR) <span
                                                class="text-danger">*</span></label>
                                        <textarea name="descriptionfr" id="descriptionfr" class="form-control @error('descriptionfr') is-invalid @enderror" maxlength="100" rows="2" required>{{ old('descriptionfr', $actualite->descriptionfr) }}</textarea>
                                        @error('descriptionfr')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="contenufr" class="form-label">{{ __('actualite.contenu') }} (FR) <span
                                                class="text-danger">*</span></label>
                                        <textarea id="contenufr" name="contenufr" class="form-control mb-3 @error('contenufr') is-invalid @enderror" rows="6" required>{{ old('contenufr', $actualite->contenufr) }}</textarea>
                                        @error('contenufr')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <p> {{ __('actualite.rendu_markdown') }} :</p>
                                        <div id="renducontenufr"></div>
                                    </div>
                                </div>
                                {{-- EUS --}}
                                <div class="tab-pane fade" id="eus">
                                    <div class="mb-3">
                                        <label for="titreeus" class="form-label">{{ __('actualite.titre') }} (EUS) <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="titreeus" id="titreeus" class="form-control @error('titreeus') is-invalid @enderror" maxlength="30"
                                            value="{{ old('titreeus', $actualite->titreeus) }}" required>
                                        @error('titreeus')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="descriptioneus" class="form-label">{{ __('actualite.description') }} (EUS) <span
                                                class="text-danger">*</span></label>
                                        <textarea name="descriptioneus" id="descriptioneus" class="form-control @error('descriptioneus') is-invalid @enderror" maxlength="100" rows="2" required>{{ old('descriptioneus', $actualite->descriptioneus) }}</textarea>
                                        @error('descriptioneus')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="contenueus" class="form-label">{{ __('actualite.contenu') }} (EUS) <span
                                                class="text-danger">*</span></label>
                                        <textarea id="contenueus" name="contenueus" class="form-control @error('contenueus') is-invalid @enderror" rows="6" required>{{ old('contenueus', $actualite->contenueus) }}</textarea>
                                        @error('contenueus')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <p> {{ __('actualite.rendu_markdown') }} :</p>
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
                                <select id="type" name="type" class="form-select @error('type') is-invalid @enderror">
                                    <option value="public"
                                        {{ old('type', $actualite->type) == 'public' ? 'selected' : '' }}>
                                        {{ __('actualite.public') }}</option>
                                    <option value="private"
                                        {{ old('type', $actualite->type) == 'private' ? 'selected' : '' }}>
                                        {{ __('actualite.prive') }}</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- DATEPICKER --}}
                            <div class="mb-3 position-relative" id="custom-datepicker-wrapper">
                                <label for="dateP" class="form-label fw-bold">{{ __('actualite.date_publication') }}
                                    <span class="text-danger">*</span></label>
                                <input type="text" id="dateP" name="dateP"
                                    class="form-control bg-white cursor-pointer @error('dateP') is-invalid @enderror"
                                    value="{{ old('dateP', $actualite->dateP ? $actualite->dateP->format('d/m/Y') : date('d/m/Y')) }}"
                                    readonly required placeholder="Sélectionner une date" onclick="toggleCalendar()">
                                @error('dateP')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
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
                                    <div class="datepicker-grid" id="calendar-grid"></div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="lien"
                                    class="form-label fw-bold">{{ __('actualite.lien_externe') }}</label>
                                <input type="url" id="lien" name="lien" class="form-control @error('lien') is-invalid @enderror"
                                    value="{{ old('lien', $actualite->lien) }}" placeholder="https://...">
                                @error('lien')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="archive" name="archive"
                                    {{ $actualite->archive ? 'checked' : '' }}>
                                <label class="form-check-label" for="archive">{{ __('actualite.archiver') }}</label>
                            </div>

                            {{-- ETIQUETTES --}}
                            <div class="mb-3 position-relative">
                                <label for="real-tag-select" class="form-label fw-bold">{{ __('actualite.etiquettes') }}</label>
                                <select name="etiquettes[]" id="real-tag-select" multiple class="d-none">
                                    @foreach ($etiquettes as $tag)
                                        <option value="{{ $tag->idEtiquette }}"
                                            {{ $actualite->etiquettes->contains('idEtiquette', $tag->idEtiquette) ? 'selected' : '' }}>
                                            {{ $tag->nom }}</option>
                                    @endforeach
                                </select>
                                <div class="form-control p-2 @error('etiquettes') is-invalid @enderror" style="min-height: 45px;">
                                    <div id="selected-tags-container" class="d-flex flex-wrap gap-1 mb-1"></div>
                                    <input type="text" id="tag-search-input" class="border-0 w-100 p-0"
                                        style="outline: none;" placeholder="{{ __('actualite.tag_search_placeholder') }}">
                                </div>
                                <div id="tag-suggestions" class="list-group position-absolute w-100 shadow mt-1"
                                    style="display: none; z-index: 1000;"></div>
                                @error('etiquettes')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- IMAGES --}}
                            <div class="mb-4">
                                <label for="images" class="form-label fw-bold">{{ __('actualite.images') }}</label>

                                {{-- IMAGES EXISTANTES --}}
                                @if ($actualite->documents->where('type', 'image')->count() > 0)
                                    <div class="mb-2 p-3 bg-white rounded border">
                                        <small class="text-muted d-block mb-2 fw-bold">Images actuelles :</small>
                                        <div class="d-flex flex-wrap gap-3">
                                            @foreach ($actualite->documents->where('type', 'image') as $doc)
                                                <div class="existing-image-container">
                                                    {{-- L'image déclenche maintenant la fonction openZoomImage --}}
                                                    <img src="{{ asset('storage/' . $doc->chemin) }}"
                                                        class="rounded shadow-sm border clickable-image"
                                                        style="width: 70px; height: 70px; object-fit: cover;"
                                                        onclick="openZoomImage(this.src)"
                                                        onkeydown="if(event.key === 'Enter' || event.key === ' ') { openZoomImage(this.src); event.preventDefault(); }"
                                                        role="button" tabindex="0" aria-label="Agrandir l'image" alt="Actualité">

                                                    {{-- Bouton suppression déclenche la Modale Bootstrap --}}
                                                    <button type="button" class="btn-delete-image"
                                                        onclick="openDeleteModal('del-img-{{ $doc->idDocument }}')">
                                                        &times;
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <input type="file" id="images" name="images[]" class="form-control mb-2"
                                    multiple accept="image/*">

                                {{-- Message d'erreur serveur pour images trop lourdes ou autres erreurs de validation --}}
                                @if ($errors->has('images') || $errors->has('images.*'))
                                    <div class="alert alert-danger mt-2">
                                        @foreach ($errors->get('images') as $err)
                                            <div>{{ $err }}</div>
                                        @endforeach
                                        @foreach ($errors->get('images.*') as $err)
                                            <div>{{ $err }}</div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Message d'erreur client (taille) --}}
                                <div id="image-error" class="alert alert-danger mt-2 d-none" role="alert"></div>

                                <div id="image-preview-container" class="row g-2 bg-white p-2 rounded border"
                                    style="display:none;"></div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" id="submitBtn" class="btn btn-warning py-2"
                                    disabled>{{ __('actualite.mettre_a_jour') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- FORMULAIRES CACHÉS POUR SUPPRESSION D'IMAGE --}}
        @foreach ($actualite->documents as $doc)
            <form id="del-img-{{ $doc->idDocument }}"
                action="{{ route('admin.actualites.detachDocument', ['idActualite' => $actualite->idActualite, 'idDocument' => $doc->idDocument]) }}"
                method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        @endforeach
    </div>

    {{-- MODAL ZOOM IMAGE (Utilisée pour Preview ET Images existantes) --}}
    <div class="modal fade" id="imageZoomModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0">
                {{-- On utilise flex pour centrer le conteneur dans la page --}}
                <div class="modal-body p-0 d-flex justify-content-center align-items-center">

                    {{-- CONTENEUR WRAPPER : d-inline-block ajuste sa largeur à l'image --}}
                    <div class="position-relative d-inline-block">

                        {{-- L'IMAGE --}}
                        <img id="modalImageFull" src="" class="img-fluid rounded shadow"
                            style="max-height: 90vh;" alt="Agrandie">

                        {{-- LE BOUTON : Placé APRES l'image dans le code pour le z-index naturel --}}
                        {{-- J'ai ajouté un petit fond noir semi-transparent pour qu'on voie la croix même sur une image blanche --}}
                        <button type="button" class="btn-close btn-close position-absolute top-0 end-0 m-2"
                            data-bs-dismiss="modal" aria-label="Close"
                            style="z-index: 10; ">
                        </button>

                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- MODAL CONFIRMATION SUPPRESSION --}}
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-danger">{{ __('actualite.supprimer_image') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('actualite.confirmer_retrait_image') }}</p>
                    <p class="small text-muted mb-0">{{ __('actualite.action_irreversible') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('actualite.annuler') }}</button>
                    {{-- Le bouton de confirmation qui soumettra le formulaire --}}
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">{{ __('actualite.confirmer_suppression') }}</button>
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

            // --- GESTION MODALE ZOOM (Global) ---
            const zoomModal = new bootstrap.Modal(document.getElementById('imageZoomModal'));
            const fullImage = document.getElementById('modalImageFull');

            // Fonction exposée pour ouvrir le zoom depuis n'importe où (preview ou existante)
            window.openZoomImage = function(src) {
                fullImage.src = src;
                zoomModal.show();
            }


            // --- GESTION MODALE SUPPRESSION (Global) ---
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            let formIdToDelete = null;

            // 1. Fonction appelée par le bouton "X"
            window.openDeleteModal = function(formId) {
                formIdToDelete = formId; // On stocke l'ID du formulaire à soumettre
                deleteModal.show(); // On affiche la modale
            }

            // 2. Clic sur "Confirmer" dans la modale
            document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
                if (formIdToDelete) {
                    document.getElementById(formIdToDelete).submit();
                }
            });


            // --- DATEPICKER ---
            const dateInput = document.getElementById('dateP');
            const calendarPopup = document.getElementById('calendar-popup');
            const grid = document.getElementById('calendar-grid');
            const monthSelect = document.getElementById('monthSelect');
            const yearSelect = document.getElementById('yearSelect');
            const monthNames = @json($monthNames);
            const dayNames = @json($dayNames);

            function parseDateFR(dateStr) {
                if (!dateStr) return new Date();
                const parts = dateStr.split('/');
                if (parts.length === 3) return new Date(parts[2], parts[1] - 1, parts[0]);
                return new Date();
            }

            let currentDate = new Date();
            let selectedDate = new Date();
            if (dateInput.value) {
                const parsed = parseDateFR(dateInput.value);
                selectedDate = parsed;
                currentDate = new Date(parsed);
            }

            function renderCalendar() {
                grid.innerHTML = "";
                monthSelect.value = currentDate.getMonth();
                yearSelect.value = currentDate.getFullYear();
                dayNames.forEach(name => {
                    const div = document.createElement('div');
                    div.className = 'day-name';
                    div.innerText = name;
                    grid.appendChild(div);
                });
                const year = currentDate.getFullYear();
                const month = currentDate.getMonth();
                const firstDay = new Date(year, month, 1).getDay();
                const daysInMonth = new Date(year, month + 1, 0).getDate();
                const daysInPrevMonth = new Date(year, month, 0).getDate();

                for (let i = 0; i < firstDay; i++) {
                    const div = document.createElement('div');
                    div.className = 'day-number faded';
                    div.innerText = daysInPrevMonth - firstDay + 1 + i;
                    grid.appendChild(div);
                }
                for (let i = 1; i <= daysInMonth; i++) {
                    const div = document.createElement('div');
                    div.className = 'day-number';
                    div.innerText = i;
                    if (i === selectedDate.getDate() && month === selectedDate.getMonth() && year === selectedDate
                        .getFullYear()) {
                        div.classList.add('selected');
                    }
                    div.onclick = function() {
                        selectedDate = new Date(year, month, i);
                        const formattedDate =
                            `${String(selectedDate.getDate()).padStart(2, '0')}/${String(selectedDate.getMonth() + 1).padStart(2, '0')}/${selectedDate.getFullYear()}`;
                        dateInput.value = formattedDate;
                        dateInput.dispatchEvent(new Event('input'));
                        dateInput.dispatchEvent(new Event('change'));
                        renderCalendar();
                        calendarPopup.classList.remove('active');
                    };
                    grid.appendChild(div);
                }
            }

            window.toggleCalendar = function() {
                calendarPopup.classList.toggle('active');
                if (calendarPopup.classList.contains('active')) {
                    if (dateInput.value) {
                        const parsed = parseDateFR(dateInput.value);
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
            document.addEventListener('click', function(e) {
                const wrapper = document.getElementById('custom-datepicker-wrapper');
                if (wrapper && !wrapper.contains(e.target)) calendarPopup.classList.remove('active');
            });

            // --- ETIQUETTES ---
            const realTagSelect = document.getElementById('real-tag-select');
            const tagSearchInput = document.getElementById('tag-search-input');
            const tagSuggestions = document.getElementById('tag-suggestions');
            const tagContainer = document.getElementById('selected-tags-container');
            const allTags = Array.from(realTagSelect.options).map(opt => ({
                id: opt.value,
                name: opt.text,
                selected: opt.selected
            }));

            function renderTags() {
                tagContainer.innerHTML = '';
                allTags.forEach(tag => {
                    if (tag.selected) {
                        const badge = document.createElement('span');
                        badge.className = 'badge bg-warning text-dark d-flex align-items-center';
                        badge.innerHTML =
                            `${tag.name} <i class="bi bi-x ms-1 cursor-pointer" onclick="toggleTag('${tag.id}')"></i>`;
                        tagContainer.appendChild(badge);
                    }
                });
                Array.from(realTagSelect.options).forEach(opt => {
                    opt.selected = allTags.find(t => t.id === opt.value).selected;
                });
            }
            renderTags();

            window.toggleTag = function(id) {
                const tag = allTags.find(t => t.id === id);
                if (tag) {
                    tag.selected = !tag.selected;
                    renderTags();
                    tagSearchInput.value = '';
                    tagSuggestions.style.display = 'none';
                }
            };

            function showTagSuggestions(list) {
                tagSuggestions.innerHTML = '';
                if (!list || list.length === 0) {
                    tagSuggestions.style.display = 'none';
                    return;
                }
                tagSuggestions.style.display = 'block';
                list.forEach(tag => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'list-group-item list-group-item-action';
                    btn.innerText = tag.name;
                    btn.onclick = () => {
                        toggleTag(tag.id);
                        tagSearchInput.focus();
                    };
                    tagSuggestions.appendChild(btn);
                });
            }

            tagSearchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase();
                if (query.length === 0) {
                    tagSuggestions.style.display = 'none';
                    tagSuggestions.innerHTML = '';
                    return;
                }
                const filtered = allTags.filter(t => t.name.toLowerCase().includes(query) && !t.selected);
                showTagSuggestions(filtered);
            });

            tagSearchInput.addEventListener('focus', function() {
                const available = allTags.filter(t => !t.selected);
                showTagSuggestions(available);
            });

            tagSearchInput.addEventListener('click', function(e) {
                e.stopPropagation();
                if (this.value.trim().length === 0) {
                    const available = allTags.filter(t => !t.selected);
                    showTagSuggestions(available);
                }
            });

            document.addEventListener('click', e => {
                if (!tagSearchInput.contains(e.target) && !tagSuggestions.contains(e.target)) tagSuggestions
                    .style.display = 'none';
            });

            // --- IMAGES (Preview des nouvelles) ---
            const imageInput = document.getElementById('images');
            const previewContainer = document.getElementById('image-preview-container');

            imageInput.addEventListener('change', function() {
                previewContainer.innerHTML = '';
                const maxBytes = 2048 * 1024; // 2MB en octets (correspond au max de la validation)
                const tooLarge = [];

                if (this.files.length === 0) {
                    previewContainer.style.display = 'none';
                    document.getElementById('image-error').classList.add('d-none');
                    validateForm();
                    return;
                }

                Array.from(this.files).forEach(file => {
                    if (file.size > maxBytes) {
                        tooLarge.push({ name: file.name, size: file.size });
                    }
                });

                const imageErrorEl = document.getElementById('image-error');
                if (tooLarge.length > 0) {
                    // Afficher message d'erreur et désactiver le bouton de soumission
                    const list = tooLarge.map(f => `${f.name} (${Math.round(f.size / 1024)} KB)`).join(', ');
                    const imgTooLargeTpl = {!! json_encode(__('actualite.images_trop_lourdes', ['list' => '__LIST__', 'max' => '__MAX__'])) !!};
                    imageErrorEl.textContent = imgTooLargeTpl.replace('__LIST__', list).replace('__MAX__', '2048');
                    imageErrorEl.classList.remove('d-none');
                    submitBtn.disabled = true;
                    previewContainer.style.display = 'none';
                    return;
                } else {
                    imageErrorEl.classList.add('d-none');
                    submitBtn.disabled = false;
                }

                previewContainer.style.display = 'flex';
                previewContainer.style.flexWrap = 'wrap';
                Array.from(this.files).forEach(file => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = e => {
                            const col = document.createElement('div');
                            col.className = 'col-3 position-relative';
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.className = 'img-thumbnail w-100 shadow-sm';
                            img.style.height = '80px';
                            img.style.objectFit = 'cover';
                            img.style.cursor = 'zoom-in';

                            // On réutilise la même fonction globale de zoom !
                            img.onclick = () => {
                                window.openZoomImage(e.target.result);
                            };

                            col.appendChild(img);
                            previewContainer.appendChild(col);
                        };
                        reader.readAsDataURL(file);
                    }
                });
                validateForm();
            });

            // --- VALIDATION ---
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
                document.querySelectorAll('#fr [required]').forEach(i => {
                    if (!i.checkValidity()) frValid = false;
                });
                errFr.style.display = frValid ? 'none' : 'inline';
                frValid ? tabFrBtn.classList.remove('text-danger') : tabFrBtn.classList.add('text-danger');
                let eusValid = true;
                document.querySelectorAll('#eus [required]').forEach(i => {
                    if (!i.checkValidity()) eusValid = false;
                });
                errEus.style.display = eusValid ? 'none' : 'inline';
                eusValid ? tabEusBtn.classList.remove('text-danger') : tabEusBtn.classList.add('text-danger');
            }
            form.addEventListener('input', validateForm);
            form.addEventListener('change', validateForm);
            validateForm();

            // --- MARKDOWN PREVIEW ---
            if (typeof AfficherMarkdownfromBalise === "function") {
                AfficherMarkdownfromBalise('contenufr', 'renducontenufr');
                AfficherMarkdownfromBalise('contenueus', 'renducontenueus');
                document.getElementById('contenufr').addEventListener('input', () => AfficherMarkdownfromBalise(
                    'contenufr', 'renducontenufr'));
                document.getElementById('contenueus').addEventListener('input', () => AfficherMarkdownfromBalise(
                    'contenueus', 'renducontenueus'));
            }
        });
    </script>
</x-app-layout>
