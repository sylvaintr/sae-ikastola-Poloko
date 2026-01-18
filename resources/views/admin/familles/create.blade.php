<x-app-layout>
    @php
        $isEdit = isset($famille);
        $idFamille = $isEdit ? $famille->idFamille : null;
        $defaultRatio = 50;
        $countEnfants = $isEdit ? $famille->enfants->count() : 0;

        if ($isEdit && $famille->utilisateurs->count() > 0) {
            $p1 = $famille->utilisateurs->first();
            if ($p1->pivot && $p1->pivot->parite !== null) {
                $defaultRatio = $p1->pivot->parite;
            }
        }
    @endphp

    <div class="container py-5">
        <div class="mb-5">
            <h2 class="fw-bolder mb-0 text-break">
                @if($isEdit)
                    {{ __('famille.edit_title', ['id' => $famille->idFamille], 'eus') }}
                @else
                    {{ __('famille.management_title', [], 'eus') }}
                @endif
            </h2>
            @if (Lang::getLocale() == 'fr')
                <small class="text-muted d-block">
                    @if($isEdit)
                        {{ __('famille.edit_title', ['id' => $famille->idFamille]) }}
                    @else
                        {{ __('famille.management_title') }}
                    @endif
                </small>
            @endif
        </div>

        <form id="mainForm" onsubmit="return false;" class="admin-form">
            @csrf

            <div class="row g-4 mb-4">
                <div class="col-12 col-md-6">
                    {{-- Section Parents --}}
                    <div class="mb-3">
                        <h3 class="fw-bold mb-0">{{ __('famille.parent_label', [], 'eus') }}ak | {{ __('famille.parent_label') }}s</h3>
                        @if (Lang::getLocale() == 'fr')
                            <small class="text-muted d-block mb-3">{{ __('famille.parent_label') }}s disponibles</small>
                        @endif
                    </div>

                    <label for="parent-search" class="form-label small text-muted fw-bold">
                        {{ __('famille.search_label', [], 'eus') }}
                        @if (Lang::getLocale() == 'fr') | {{ __('famille.search_label') }} @endif
                    </label>
                    <input type="text"
                           id="parent-search"
                           class="form-control mb-2"
                           placeholder="{{ __('famille.search_ajax_placeholder', [], 'eus') }}"
                           onkeyup="searchParentsAJAX(this.value)">

                    <div id="available-parents" class="border rounded p-3 bg-white shadow-sm" style="height: auto; max-height: 500px; overflow-y: auto;">
                        @if($isEdit)
                            @foreach($famille->utilisateurs as $user)
                                <button type="button"
                                        class="role-item d-flex align-items-center p-2 mb-2 border rounded bg-white hover-shadow w-100 text-start"
                                        onclick="addRole({{ $user->idUtilisateur }}, '{{ $user->nom }} {{ $user->prenom }}')">
                                    <span class="text-dark item-name text-truncate me-2">{{ $user->nom }} {{ $user->prenom }}</span>
                                    <div class="ms-auto d-flex align-items-center text-secondary">
                                        <span class="me-3 small fw-bold">{{ __('famille.parent_label', [], 'eus') }}</span>
                                        <i class="bi bi-plus-circle text-dark fs-5"></i>
                                    </div>
                                </button>
                            @endforeach
                        @else
                            @if(isset($tousUtilisateurs))
                                @foreach($tousUtilisateurs as $user)
                                    <button type="button"
                                            class="role-item d-flex align-items-center p-2 mb-2 border rounded bg-white hover-shadow w-100 text-start"
                                            onclick="addRole({{ $user->idUtilisateur }}, '{{ $user->nom }} {{ $user->prenom }}')">
                                        <span class="text-dark item-name text-truncate me-2">{{ $user->nom }} {{ $user->prenom }}</span>
                                        <div class="ms-auto d-flex align-items-center text-secondary">
                                            <span class="me-3 small fw-bold">{{ __('famille.parent_label', [], 'eus') }}</span>
                                            <i class="bi bi-plus-circle text-dark fs-5"></i>
                                        </div>
                                    </button>
                                @endforeach
                            @endif
                        @endif
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    {{-- Section Enfants --}}
                    <div class="mb-3">
                        <h3 class="fw-bold mb-0">{{ __('famille.child_label', [], 'eus') }}ak | {{ __('famille.child_label') }}s</h3>
                        @if (Lang::getLocale() == 'fr')
                            <small class="text-muted d-block mb-3">{{ __('famille.child_label') }}s disponibles</small>
                        @endif
                    </div>

                    <label for="enfant-search" class="form-label small text-muted fw-bold">
                        {{ __('famille.search_label', [], 'eus') }}
                        @if (Lang::getLocale() == 'fr') | {{ __('famille.search_label') }} @endif
                    </label>
                    <input type="text"
                           id="enfant-search"
                           class="form-control mb-2"
                           placeholder="{{ __('famille.search_ajax_placeholder', [], 'eus') }}"
                           onkeyup="searchEnfantsAJAX(this.value)">

                    <div id="available-enfants" class="border rounded p-3 bg-white shadow-sm" style="height: auto; max-height: 500px; overflow-y: auto;">
                        @if($isEdit)
                            @foreach($famille->enfants as $enfant)
                                <button type="button"
                                        class="role-item d-flex align-items-center p-2 mb-2 border rounded bg-white hover-shadow w-100 text-start"
                                        onclick="addChild({{ $enfant->idEnfant }}, '{{ $enfant->nom }} {{ $enfant->prenom }}')">
                                    <span class="text-dark item-name text-truncate me-2">{{ $enfant->nom }} {{ $enfant->prenom }}</span>
                                    <div class="ms-auto d-flex align-items-center text-secondary">
                                        <span class="me-3 small fw-bold">{{ __('famille.child_label', [], 'eus') }}</span>
                                        <i class="bi bi-plus-circle text-dark fs-5"></i>
                                    </div>
                                </button>
                            @endforeach
                        @else
                            @if(isset($tousEnfants))
                                @foreach($tousEnfants as $enfant)
                                    <button type="button"
                                            class="role-item d-flex align-items-center p-2 mb-2 border rounded bg-white hover-shadow w-100 text-start"
                                            onclick="addChild({{ $enfant->idEnfant }}, '{{ $enfant->nom }} {{ $enfant->prenom }}')">
                                        <span class="text-dark item-name text-truncate me-2">{{ $enfant->nom }} {{ $enfant->prenom }}</span>
                                        <div class="ms-auto d-flex align-items-center text-secondary">
                                            <span class="me-3 small fw-bold">{{ __('famille.child_label', [], 'eus') }}</span>
                                            <i class="bi bi-plus-circle text-dark fs-5"></i>
                                        </div>
                                    </button>
                                @endforeach
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            {{-- Section unique pour les éléments sélectionnés --}}
            <div class="mb-4 mt-5">
                <h6 class="form-label small text-muted mb-2 fw-bold">
                    {{ __('famille.selected_users', [], 'eus') }}*
                    @if (Lang::getLocale() == 'fr') | {{ __('famille.selected_users') }}* @endif
                </h6>
                <div id="selected-roles" class="border rounded p-3 bg-light" style="min-height: 200px; max-height: 400px; overflow-y: auto;">
                    <div class="role-list-empty-message text-muted text-center mt-5">
                        {{ __('famille.click_to_select', [], 'eus') }}
                        @if (Lang::getLocale() == 'fr') <br><small>{{ __('famille.click_to_select') }}</small> @endif
                    </div>
                </div>
            </div>

            <div id="financial-section" style="display: none;" class="mt-4 mb-4">
                <div x-data="{ ratio: {{ $defaultRatio }} }">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center w-100">
                        <div class="mb-3 mb-lg-0 text-center text-lg-start">
                            <h5 class="mb-0 fw-bold">
                                {{ __('famille.financial_split', [], 'eus') }}
                                @if (Lang::getLocale() == 'fr')
                                    <br><small class="text-muted fw-normal fs-6">{{ __('famille.financial_split') }}</small>
                                @endif
                            </h5>
                        </div>

                        <div class="flex-grow-1 w-100 px-2 ms-lg-5">
                            <div class="d-flex align-items-center justify-content-center justify-content-lg-start">
                                <span id="label-parent-1" class="fw-bold text-secondary text-truncate text-end" style="width: 80px; min-width: 60px;">P1</span>
                                <div class="d-flex flex-column align-items-center flex-grow-1 mx-3" style="max-width: 250px;">
                                    <div class="border rounded px-2 py-1 bg-white d-flex align-items-center shadow-sm w-100">
                                        <input type="range"
                                               id="range-parite"
                                               class="form-range my-1"
                                               min="0"
                                               max="100"
                                               x-model="ratio"
                                               x-ref="sliderParite"
                                               style="accent-color: orange;">
                                    </div>
                                    <div class="mt-1 fw-bolder fs-5 text-dark">
                                        <span x-text="ratio">{{ $defaultRatio }}</span> / <span x-text="100 - ratio">{{ 100 - $defaultRatio }}</span>
                                    </div>
                                </div>
                                <span id="label-parent-2" class="fw-bold text-secondary text-truncate text-start" style="width: 80px; min-width: 60px;">P2</span>
                            </div>
                        </div>

                        <div class="mt-3 mt-lg-0 ms-lg-4 d-flex justify-content-center justify-content-lg-end">
                            <div class="d-flex gap-2 w-100 w-lg-auto">
                                <a href="{{ route('admin.familles.index')}}"
                                   class="btn px-3 py-2 fw-bold flex-fill flex-lg-grow-0 text-center"
                                   style="background:white; border:1px solid orange; color:orange;">
                                    {{ __('famille.cancel', [], 'eus') }}
                                </a>
                                <button type="button"
                                        class="btn px-3 py-2 fw-bold flex-fill flex-lg-grow-0"
                                        style="background:orange; color:white; border:1px solid orange;"
                                        @if($isEdit) onclick="saveParityOnly({{ $idFamille }})" @else onclick="createFamily()" @endif>
                                    {{ __('famille.save', [], 'eus') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Boutons d'action toujours visibles --}}
            <div class="mt-4 d-flex flex-column align-items-end">
                <div class="d-flex gap-2 w-100 w-lg-auto justify-content-end">
                    <a href="{{ route('admin.familles.index')}}"
                       class="btn px-3 py-2 fw-bold flex-fill flex-lg-grow-0 text-center"
                       style="background:white; border:1px solid orange; color:orange;">
                        {{ __('famille.cancel', [], 'eus') }}
                    </a>
                    <button type="button"
                            id="btnSaveFamily"
                            class="btn px-3 py-2 fw-bold flex-fill flex-lg-grow-0"
                            style="background:orange; color:white; border:1px solid orange;"
                            @if($isEdit) onclick="saveParityOnly({{ $idFamille }})" @else onclick="createFamily()" @endif>
                        {{ __('famille.save', [], 'eus') }}
                    </button>
                </div>
                @if (Lang::getLocale() == 'fr')
                    <div class="d-flex justify-content-center mt-1" style="width: 100%; max-width: fit-content;">
                        <small class="text-muted">
                            {{ __('famille.cancel') }} | {{ __('famille.save') }}
                        </small>
                    </div>
                @endif
            </div>
        </form>
    </div>

    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header border-0 pb-0 ps-4 pt-4">
                    <h5 id="modalTitle" class="modal-title fw-bold fs-4 text-dark">Confirmation</h5>
                </div>
                <div id="modalMessage" class="modal-body ps-4 pe-4 pt-2 text-secondary">Action ?</div>
                <div class="modal-footer border-0 pe-4 pb-4">
                    <div class="d-flex flex-column align-items-end w-100">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn px-4 py-2 fw-bold" data-bs-dismiss="modal" style="background: white; border: 1px solid orange; color: orange; border-radius: 6px;">
                                {{ __('famille.cancel', [], 'eus') }}
                            </button>
                            <button type="button" id="btnConfirmSave" class="btn px-4 py-2 fw-bold text-white" style="background: orange; border: 1px solid orange; border-radius: 6px;">
                                {{ __('famille.validate', [], 'eus') }}
                            </button>
                        </div>
                        @if (Lang::getLocale() == 'fr')
                            <div class="d-flex justify-content-end mt-1">
                                <small class="text-muted">
                                    {{ __('famille.cancel') }} | {{ __('famille.validate') }}
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-body text-center p-5">
                    <div class="mb-4 text-success">
                        <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                    <h4 class="fw-bold mb-3">{{ __('famille.success_msg', [], 'eus') }}</h4>
                    @if (Lang::getLocale() == 'fr') <p class="text-muted">{{ __('famille.success_msg') }}</p> @endif
                    <button type="button" id="btnSuccessOk" class="btn px-5 py-2 fw-bold text-white" style="background: orange; border: 1px solid orange; border-radius: 6px;">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const translations = {
            errorNoChildren: { eus: "{{ __('famille.error_no_children', [], 'eus') }}", fr: "{{ __('famille.error_no_children', [], 'fr') }}" },
            errorSelection: { eus: "{{ __('famille.error_selection', [], 'eus') }}", fr: "{{ __('famille.error_selection', [], 'fr') }}" },
            confirmCreateTitle: { eus: "{{ __('famille.confirm_create_title', [], 'eus') }}", fr: "{{ __('famille.confirm_create_title', [], 'fr') }}" },
            confirmCreateMsg: { eus: "{{ __('famille.confirm_create_msg', [], 'eus') }}", fr: "{{ __('famille.confirm_create_msg', [], 'fr') }}" },
            confirmParityTitle: { eus: "{{ __('famille.confirm_parity_title', [], 'eus') }}", fr: "{{ __('famille.confirm_parity_title', [], 'fr') }}" },
            confirmParityMsg: { eus: "{{ __('famille.confirm_parity_msg', [], 'eus') }}", fr: "{{ __('famille.confirm_parity_msg', [], 'fr') }}" },
            parentLabel: "{{ __('famille.parent_label', [], 'eus') }}",
            childLabel: "{{ __('famille.child_label', [], 'eus') }}",
            emptyMsg: "{{ __('famille.click_to_select', [], 'eus') }}",
            noUserFound: "{{ __('famille.no_results', [], 'eus') }}"
        };

        const showFrench = "{{ Lang::getLocale() }}" === 'fr';
        const selectedRoles = document.getElementById('selected-roles');
        const financialSection = document.getElementById('financial-section');
        const labelP1 = document.getElementById('label-parent-1');
        const labelP2 = document.getElementById('label-parent-2');

        let pendingData = null;
        let isCreateMode = false;
        let initialParentsHTML = '';
        let initialEnfantsHTML = '';

        const dbRatio = {{ $defaultRatio }};
        const nbEnfantsInitial = {{ $countEnfants }};
        const isEditMode = {{ $isEdit ? 'true' : 'false' }};

        function escapeHtml(text) {
            if (!text) return '';
            const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
            return String(text).replace(/[&<>"']/g, m => map[m]);
        }

        function showConfirm(titleObj, msgObj) {
            const titleElem = document.getElementById('modalTitle');
            const msgElem = document.getElementById('modalMessage');
            titleElem.innerHTML = `<span>${titleObj.eus}</span>` + (showFrench ? `<br><small class="text-muted fw-normal fs-6">${titleObj.fr}</small>` : "");
            msgElem.innerHTML = `<span>${msgObj.eus}</span>` + (showFrench ? `<br><small class="text-muted">${msgObj.fr}</small>` : "");
            new globalThis.bootstrap.Modal(document.getElementById('confirmationModal')).show();
        }

        function searchParentsAJAX(query) {
            const container = document.getElementById('available-parents');

            if (query.trim().length === 0) {
                container.innerHTML = initialParentsHTML;
                return;
            }

            const url = "{{ url('/api/search/users') }}";

            fetch(`${url}?q=${encodeURIComponent(query)}`)
                .then(res => {
                    if (!res.ok) throw new Error("Erreur HTTP " + res.status);
                    return res.json();
                })
                .then(data => {
                    container.innerHTML = '';

                    if (data.length === 0) {
                        container.innerHTML = `<div class="text-muted small fst-italic p-2">${translations.noUserFound}</div>`;
                        return;
                    }

                    data.forEach(user => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'role-item d-flex align-items-center p-2 mb-2 border rounded bg-white hover-shadow w-100 text-start';
                        btn.onclick = () => addRole(user.idUtilisateur, user.nom + ' ' + user.prenom);

                        btn.innerHTML = `
                            <span class="text-dark item-name text-truncate me-2">${escapeHtml(user.nom + ' ' + user.prenom)}</span>
                            <div class="ms-auto d-flex align-items-center text-secondary">
                                <span class="me-3 small fw-bold">${translations.parentLabel}</span>
                                <i class="bi bi-plus-circle text-dark fs-5"></i>
                            </div>
                        `;
                        container.appendChild(btn);
                    });
                })
                .catch(err => console.error("Erreur de recherche:", err));
        }

        function searchEnfantsAJAX(query) {
            const container = document.getElementById('available-enfants');
            const lowerQuery = query.toLowerCase().trim();

            if (lowerQuery.length === 0) {
                container.innerHTML = initialEnfantsHTML;
                return;
            }

            // Recherche locale dans la liste des enfants
            const allChildren = container.querySelectorAll('.role-item');
            const noResultsEl = container.querySelector('.no-results-message');
            if (noResultsEl) noResultsEl.remove();
            
            let found = false;

            allChildren.forEach(item => {
                const nameText = item.querySelector('.item-name')?.textContent?.toLowerCase() || '';
                if (nameText.includes(lowerQuery)) {
                    item.style.display = '';
                    found = true;
                } else {
                    item.style.display = 'none';
                }
            });

            if (!found && allChildren.length > 0) {
                const noResults = document.createElement('div');
                noResults.className = 'text-muted small fst-italic p-2 no-results-message';
                noResults.textContent = translations.noUserFound;
                container.appendChild(noResults);
            }
        }

        function checkParityVisibility() {
            const parentInputs = selectedRoles.querySelectorAll('input.user-id');
            const slider = document.querySelector('[x-ref="sliderParite"]');
            const alpineData = slider ? Alpine.$data(slider.closest('[x-data]')) : null;

            if (parentInputs.length > 0) {
                financialSection.style.display = 'block';
                const p1NameEl = parentInputs[0].closest('.role-item').querySelector('.item-name');
                if (p1NameEl) {
                    labelP1.innerText = p1NameEl.innerText.split(' ')[0];
                }

                if (parentInputs.length === 1) {
                    labelP2.style.display = 'none';
                    slider.value = 100;
                    if (alpineData) alpineData.ratio = 100;
                    slider.disabled = true;
                    slider.style.opacity = '0.5';
                } else {
                    const p2NameEl = parentInputs[1].closest('.role-item').querySelector('.item-name');
                    if (p2NameEl) {
                        labelP2.innerText = p2NameEl.innerText.split(' ')[0];
                        labelP2.style.display = 'inline';
                    }
                    slider.disabled = false;
                    slider.style.opacity = '1';
                    const targetVal = isEditMode ? dbRatio : 50;
                    slider.value = targetVal;
                    if (alpineData) alpineData.ratio = targetVal;
                }
                slider.dispatchEvent(new Event('input'));
            } else {
                financialSection.style.display = 'none';
            }
        }

        function addRole(id, name) {
            if (isEditMode && nbEnfantsInitial === 0) {
                alert(translations.errorNoChildren.eus + (showFrench ? "\n" + translations.errorNoChildren.fr : ""));
                return;
            }
            if (selectedRoles.querySelectorAll('input.user-id').length >= 2) return;
            if (Array.from(selectedRoles.querySelectorAll('input.user-id')).some(i => i.value == id)) return;

            clearEmptyMsg();
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'role-item d-flex align-items-center p-2 mb-1 border rounded shadow-sm w-100 text-start text-white';
            btn.style.backgroundColor = 'orange';
            btn.innerHTML = `<span class="fw-bold small item-name text-truncate me-2">${escapeHtml(name)}</span><div class="ms-auto d-flex align-items-center gap-2"><small>${translations.parentLabel}</small><b class="fs-5">&times;</b></div><input type="hidden" class="user-id" value="${id}">`;
            btn.onclick = () => { btn.remove(); checkEmpty(); checkParityVisibility(); };
            selectedRoles.appendChild(btn);
            checkParityVisibility();
        }

        function addChild(id, name) {
            if (Array.from(selectedRoles.querySelectorAll('input.child-id')).some(i => i.value == id)) return;
            clearEmptyMsg();
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'role-item d-flex align-items-center p-2 mb-1 border rounded shadow-sm bg-white w-100 text-start';
            btn.innerHTML = `<span class="fw-bold small item-name text-dark text-truncate me-2">${escapeHtml(name)}</span><div class="ms-auto d-flex align-items-center gap-2 text-secondary"><small>${translations.childLabel}</small><b class="text-dark fs-5">&times;</b></div><input type="hidden" class="child-id" value="${id}">`;
            btn.onclick = () => { btn.remove(); checkEmpty(); };
            selectedRoles.appendChild(btn);
        }

        function clearEmptyMsg() {
            const m = selectedRoles.querySelector('.role-list-empty-message');
            if (m) m.remove();
        }

        function checkEmpty() {
            if (selectedRoles.querySelectorAll('.role-item').length === 0) {
                selectedRoles.innerHTML = `<div class="role-list-empty-message text-muted text-center mt-5">${translations.emptyMsg}</div>`;
            }
        }

        function createFamily() {
            const parents = selectedRoles.querySelectorAll('input.user-id');
            const children = selectedRoles.querySelectorAll('input.child-id');

            if (!parents.length || !children.length) {
                alert(translations.errorSelection.eus + (showFrench ? "\n" + translations.errorSelection.fr : ""));
                return;
            }

            const slider = document.querySelector('[x-ref="sliderParite"]');
            pendingData = {
                utilisateurs: Array.from(parents).map((p, i) => ({
                    idUtilisateur: p.value,
                    parite: parents.length === 2 ? (i === 0 ? slider.value : 100 - slider.value) : 100
                })),
                enfants: Array.from(children).map(c => ({ idEnfant: c.value }))
            };
            isCreateMode = true;
            showConfirm(translations.confirmCreateTitle, translations.confirmCreateMsg);
        }

        function saveParityOnly(id) {
            const slider = document.querySelector('[x-ref="sliderParite"]');
            pendingData = {
                idFamille: id,
                idUtilisateur: selectedRoles.querySelector('input.user-id').value,
                parite: slider.value
            };
            isCreateMode = false;
            showConfirm(translations.confirmParityTitle, translations.confirmParityMsg);
        }

        let isSubmitting = false;
        document.getElementById('btnConfirmSave').onclick = function() {
            const btn = document.getElementById('btnConfirmSave');
            
            // Empêcher les clics multiples
            if (isSubmitting) {
                return;
            }
            
            isSubmitting = true;
            btn.disabled = true;
            btn.style.opacity = '0.6';
            btn.style.cursor = 'not-allowed';
            
            const url = isCreateMode ? "{{ route('admin.familles.store') }}" : "{{ route('admin.lier.updateParite') }}";
            fetch(url, {
                method: isCreateMode ? 'POST' : 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(pendingData)
            }).then(() => {
                if (isCreateMode) {
                    // Redirection directe vers la liste des familles après création
                    window.location.href = "{{ route('admin.familles.index') }}";
                } else {
                    // Pour la mise à jour de parité, fermer la modale de confirmation
                    globalThis.bootstrap.Modal.getInstance(document.getElementById('confirmationModal')).hide();
                    new globalThis.bootstrap.Modal(document.getElementById('successModal')).show();
                    document.getElementById('btnSuccessOk').onclick = () => window.location.href = "{{ route('admin.familles.index') }}";
                    isSubmitting = false;
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    btn.style.cursor = 'pointer';
                }
            }).catch(() => {
                // En cas d'erreur, réactiver le bouton
                isSubmitting = false;
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.style.cursor = 'pointer';
            });
        };

        document.addEventListener('DOMContentLoaded', () => {
            checkParityVisibility();
            const parentsContainer = document.getElementById('available-parents');
            const enfantsContainer = document.getElementById('available-enfants');
            if (parentsContainer) {
                initialParentsHTML = parentsContainer.innerHTML;
            }
            if (enfantsContainer) {
                initialEnfantsHTML = enfantsContainer.innerHTML;
            }
        });
    </script>
</x-app-layout>

