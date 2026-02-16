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

        // PHP 8.4: sécuriser les opérations arithmétiques (éviter int - string)
        $defaultRatio = is_numeric($defaultRatio) ? (int) $defaultRatio : 50;
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
                        @php
                            $parentsDisponibles = $tousUtilisateurs
                                ?? ($isEdit ? $famille->utilisateurs : collect());
                        @endphp

                        @foreach($parentsDisponibles as $user)
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
                        @php
                            $enfantsDisponibles = $tousEnfants
                                ?? ($isEdit ? $famille->enfants : collect());
                        @endphp

                        @foreach($enfantsDisponibles as $enfant)
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
                    @if($isEdit)
                        @foreach($famille->utilisateurs as $user)
                            <button type="button"
                                    class="role-item d-flex align-items-center p-2 mb-1 border rounded shadow-sm w-100 text-start text-white"
                                    style="background-color: orange"
                                    onclick="this.remove(); checkEmpty(); checkParityVisibility();">
                                <span class="fw-bold small item-name text-truncate me-2">{{ $user->nom }} {{ $user->prenom }}</span>
                                <div class="ms-auto d-flex align-items-center gap-2">
                                    <small>{{ __('famille.parent_label', [], 'eus') }}</small>
                                    <b class="fs-5">&times;</b>
                                </div>
                                <input type="hidden" class="user-id" value="{{ $user->idUtilisateur }}">
                            </button>
                        @endforeach

                        @foreach($famille->enfants as $enfant)
                            <button type="button"
                                    class="role-item d-flex align-items-center p-2 mb-1 border rounded shadow-sm bg-white w-100 text-start"
                                    onclick="this.remove(); checkEmpty();">
                                <span class="fw-bold small item-name text-dark text-truncate me-2">{{ $enfant->nom }} {{ $enfant->prenom }}</span>
                                <div class="ms-auto d-flex align-items-center gap-2 text-secondary">
                                    <small>{{ __('famille.child_label', [], 'eus') }}</small>
                                    <b class="text-dark fs-5">&times;</b>
                                </div>
                                <input type="hidden" class="child-id" value="{{ $enfant->idEnfant }}">
                            </button>
                        @endforeach

                        @if($famille->utilisateurs->isEmpty() && $famille->enfants->isEmpty())
                            <div class="role-list-empty-message text-muted text-center mt-5">
                                {{ __('famille.click_to_select', [], 'eus') }}
                                @if (Lang::getLocale() == 'fr') <br><small>{{ __('famille.click_to_select') }}</small> @endif
                            </div>
                        @endif
                    @else
                        <div class="role-list-empty-message text-muted text-center mt-5">
                            {{ __('famille.click_to_select', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr') <br><small>{{ __('famille.click_to_select') }}</small> @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Aîné dans une autre école Seaska --}}
            <div class="mb-4">
                <div class="border rounded p-3 bg-white shadow-sm">
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input"
                               type="checkbox"
                               role="switch"
                               id="aineDansAutreSeaska"
                               aria-checked="{{ ($isEdit ? (bool) $famille->aineDansAutreSeaska : false) ? 'true' : 'false' }}"
                               @checked($isEdit ? (bool) $famille->aineDansAutreSeaska : false)>
                        <label class="form-check-label fw-bold" for="aineDansAutreSeaska">
                            {{ __('famille.aine_other_seaska_label', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr')
                                <br><small class="text-muted fw-normal">{{ __('famille.aine_other_seaska_label') }}</small>
                            @endif
                        </label>
                    </div>
                    <div class="mt-2 small text-muted">
                        {{ __('famille.aine_other_seaska_help', [], 'eus') }}
                        @if (Lang::getLocale() == 'fr')
                            <br>{{ __('famille.aine_other_seaska_help') }}
                        @endif
                    </div>
                </div>
            </div>

            <div id="financial-section" class="mt-4 mb-4">
                <div x-data="{ ratio: {{ (int) $defaultRatio }} }">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center w-100">
                        <div class="mb-3 mb-lg-0 text-center text-lg-start">
                            <h5 class="mb-0 fw-bold">
                                {{ __('famille.financial_split', [], 'eus') }}
                                @if (Lang::getLocale() == 'fr')
                                    <br><small class="text-muted fw-normal fs-6">{{ __('famille.financial_split') }}</small>
                                @endif
                            </h5>
                            <div id="parity-empty-msg" class="small text-muted mt-2">
                                {{ __('famille.parity_requires_parent', [], 'eus') }}
                                @if (Lang::getLocale() == 'fr')
                                    <br>{{ __('famille.parity_requires_parent') }}
                                @endif
                            </div>
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
                                               style="accent-color: orange;">
                                    </div>
                                    <div class="mt-2 fw-bolder fs-5 text-dark d-flex align-items-center justify-content-center gap-2">
                                        <input type="number"
                                               min="0"
                                               max="100"
                                               step="1"
                                               class="form-control form-control-sm text-center fw-bolder"
                                               style="width: 84px;"
                                               id="parite-parent-1"
                                               aria-label="Parité parent 1"
                                               value="{{ (int) $defaultRatio }}">
                                        <span class="text-muted" id="parite-separator">/</span>
                                        <input type="number"
                                               min="0"
                                               max="100"
                                               step="1"
                                               class="form-control form-control-sm text-center fw-bolder bg-light"
                                               style="width: 84px;"
                                               id="parite-parent-2"
                                               aria-label="Parité parent 2"
                                               value="{{ 100 - (int) $defaultRatio }}"
                                               readonly
                                               tabindex="-1"
                                               aria-readonly="true"
                                               style="pointer-events: none;">
                                    </div>
                                </div>
                                <span id="label-parent-2" class="fw-bold text-secondary text-truncate text-start" style="width: 80px; min-width: 60px;">P2</span>
                            </div>
                        </div>

                        {{-- Boutons déplacés en bas de page (évite les doublons) --}}
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
                            @if($isEdit) onclick="saveFamily({{ $idFamille }})" @else onclick="createFamily()" @endif>
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
                <div id="modalLoading" class="modal-body ps-4 pe-4 pt-2 text-center" style="display: none;">
                    <output class="spinner-border text-orange" style="color: orange !important;" aria-label="Chargement en cours">
                        <span class="visually-hidden">Chargement...</span>
                    </output>
                </div>
                <div class="modal-footer border-0 pe-4 pb-4">
                    <div class="d-flex flex-column align-items-end w-100">
                        <div class="d-flex gap-2">
                            <button type="button" id="btnCancelModal" class="btn px-4 py-2 fw-bold" data-bs-dismiss="modal" style="background: white; border: 1px solid orange; color: orange; border-radius: 6px;">
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
           
            errorNoChildren: { eus: @json(__('famille.error_no_children', [], 'eus')), fr: @json(__('famille.error_no_children', [], 'fr')) },
            errorSelection: { eus: @json(__('famille.error_selection', [], 'eus')), fr: @json(__('famille.error_selection', [], 'fr')) },
            confirmCreateTitle: { eus: @json(__('famille.confirm_create_title', [], 'eus')), fr: @json(__('famille.confirm_create_title', [], 'fr')) },
            confirmCreateMsg: { eus: @json(__('famille.confirm_create_msg', [], 'eus')), fr: @json(__('famille.confirm_create_msg', [], 'fr')) },
            confirmParityTitle: { eus: @json(__('famille.confirm_parity_title', [], 'eus')), fr: @json(__('famille.confirm_parity_title', [], 'fr')) },
            confirmParityMsg: { eus: @json(__('famille.confirm_parity_msg', [], 'eus')), fr: @json(__('famille.confirm_parity_msg', [], 'fr')) },
            parentLabel: @json(__('famille.parent_label', [], 'eus')),
            childLabel: @json(__('famille.child_label', [], 'eus')),
            emptyMsg: @json(__('famille.click_to_select', [], 'eus')),
            noUserFound: @json(__('famille.no_results', [], 'eus'))
        };

        const showFrench = "{{ Lang::getLocale() }}" === 'fr';
        const selectedRoles = document.getElementById('selected-roles');
        const financialSection = document.getElementById('financial-section');
        const labelP1 = document.getElementById('label-parent-1');
        const labelP2 = document.getElementById('label-parent-2');
        const sliderParite = document.getElementById('range-parite');
        const inputPariteP1 = document.getElementById('parite-parent-1');
        const inputPariteP2 = document.getElementById('parite-parent-2');
        const parityEmptyMsg = document.getElementById('parity-empty-msg');
        const paritySeparator = document.getElementById('parite-separator');

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
            const currentFamilleId = {{ $idFamille ? (int) $idFamille : 'null' }};

            if (query.trim().length === 0) {
                container.innerHTML = initialParentsHTML;
                return;
            }

            const url = "{{ url('/api/search/users') }}";

            const familleParam = currentFamilleId ? `&famille_id=${currentFamilleId}` : '';
            fetch(`${url}?q=${encodeURIComponent(query)}${familleParam}`)
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

        function clampInt(value, min, max) {
            const n = Number.parseInt(value, 10);
            if (Number.isNaN(n)) return min;
            return Math.min(max, Math.max(min, n));
        }

        function setParity(ratio) {
            const v = clampInt(ratio, 0, 100);
            if (sliderParite) sliderParite.value = String(v);
            if (inputPariteP1) inputPariteP1.value = String(v);
            if (inputPariteP2) inputPariteP2.value = String(100 - v);
        }

        function getParity() {
            // Source de vérité : slider si présent, sinon champ P1
            if (sliderParite) return clampInt(sliderParite.value, 0, 100);
            if (inputPariteP1) return clampInt(inputPariteP1.value, 0, 100);
            return 50;
        }

        function checkParityVisibility() {
            const parentInputs = selectedRoles.querySelectorAll('input.user-id');

            const hasParents = parentInputs.length > 0;
            if (parityEmptyMsg) {
                parityEmptyMsg.style.display = hasParents ? 'none' : 'block';
            }

            // Toujours visible, mais on active/désactive selon le nombre de parents
            if (hasParents) {
                const p1NameEl = parentInputs[0].closest('.role-item').querySelector('.item-name');
                if (p1NameEl) {
                    labelP1.innerText = p1NameEl.innerText.split(' ')[0];
                }

                if (parentInputs.length === 1) {
                    labelP2.style.display = 'none';
                    if (inputPariteP2) inputPariteP2.style.display = 'none';
                    if (paritySeparator) paritySeparator.style.display = 'none';
                    setParity(100);
                    if (sliderParite) {
                        sliderParite.disabled = true;
                        sliderParite.style.opacity = '0.5';
                    }
                    if (inputPariteP1) inputPariteP1.disabled = true;
                } else {
                    const p2NameEl = parentInputs[1].closest('.role-item').querySelector('.item-name');
                    if (p2NameEl) {
                        labelP2.innerText = p2NameEl.innerText.split(' ')[0];
                        labelP2.style.display = 'inline';
                    }
                    if (inputPariteP2) inputPariteP2.style.display = '';
                    if (paritySeparator) paritySeparator.style.display = '';
                    const targetVal = isEditMode ? dbRatio : 50;
                    setParity(targetVal);
                    if (sliderParite) {
                        sliderParite.disabled = false;
                        sliderParite.style.opacity = '1';
                    }
                    if (inputPariteP1) inputPariteP1.disabled = false;
                }
            } else {
                // Aucun parent sélectionné -> désactiver les contrôles
                labelP2.style.display = 'inline';
                if (inputPariteP2) inputPariteP2.style.display = '';
                if (paritySeparator) paritySeparator.style.display = '';
                if (sliderParite) {
                    sliderParite.disabled = true;
                    sliderParite.style.opacity = '0.5';
                }
                if (inputPariteP1) inputPariteP1.disabled = true;
                setParity(50);
            }
        }

        function addRole(id, name) {
            // Ne pas bloquer l'ajout d'un parent (les enfants seront exigés au moment de l'enregistrement)
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

            const ratio = getParity();
            const aineDansAutreSeaska = !!document.getElementById('aineDansAutreSeaska')?.checked;
            pendingData = {
                utilisateurs: Array.from(parents).map((p, i) => ({
                    idUtilisateur: p.value,
                    parite: parents.length === 2 ? (i === 0 ? ratio : 100 - ratio) : 100
                })),
                enfants: Array.from(children).map(c => ({ idEnfant: c.value })),
                aineDansAutreSeaska
            };
            isCreateMode = true;
            showConfirm(translations.confirmCreateTitle, translations.confirmCreateMsg);
        }

        function saveFamily(id) {
            const parents = selectedRoles.querySelectorAll('input.user-id');
            const children = selectedRoles.querySelectorAll('input.child-id');

            if (!parents.length || !children.length) {
                alert(translations.errorSelection.eus + (showFrench ? "\n" + translations.errorSelection.fr : ""));
                return;
            }

            const ratio = getParity();
            const aineDansAutreSeaska = !!document.getElementById('aineDansAutreSeaska')?.checked;
            pendingData = {
                idFamille: id,
                utilisateurs: Array.from(parents).map((p, i) => ({
                    idUtilisateur: p.value,
                    parite: parents.length === 2 ? (i === 0 ? ratio : 100 - ratio) : 100
                })),
                enfants: Array.from(children).map(c => ({ idEnfant: c.value })),
                aineDansAutreSeaska
            };
            isCreateMode = false;
            showConfirm(translations.confirmParityTitle, translations.confirmParityMsg);
        }

        let isSubmitting = false;
        document.getElementById('btnConfirmSave').onclick = async function() {
            const btn = document.getElementById('btnConfirmSave');
            const btnCancel = document.getElementById('btnCancelModal');
            const modalMessage = document.getElementById('modalMessage');
            const modalLoading = document.getElementById('modalLoading');
            
            // Empêcher les clics multiples
            if (isSubmitting) {
                return;
            }
            
            // Feedback visuel immédiat
            isSubmitting = true;
            btn.disabled = true;
            btn.style.opacity = '0.6';
            btn.style.cursor = 'not-allowed';
            btnCancel.disabled = true;
            modalMessage.style.display = 'none';
            modalLoading.style.display = 'block';
            
            const storeUrl = "{{ route('admin.familles.store') }}";
            const updateBaseUrl = "{{ url('/admin/familles') }}";
            const url = isCreateMode ? storeUrl : `${updateBaseUrl}/${encodeURIComponent(pendingData.idFamille)}`;
            
            // Timeout pour éviter un "chargement infini" si le serveur ne répond pas
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 15000);

            try {
                const response = await fetch(url, {
                    method: isCreateMode ? 'POST' : 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify(pendingData),
                    signal: controller.signal,
                });

                if (isCreateMode) {
                    if (response.ok) {
                        window.location.href = "{{ route('admin.familles.index') }}";
                        return;
                    }
                } else {
                    if (response.ok) {
                        globalThis.bootstrap.Modal.getInstance(document.getElementById('confirmationModal'))?.hide();
                        new globalThis.bootstrap.Modal(document.getElementById('successModal')).show();
                        document.getElementById('btnSuccessOk').onclick = () => window.location.href = "{{ route('admin.familles.index') }}";
                        return;
                    }
                }

                // Erreur HTTP: essayer d'afficher un message utile
                const contentType = response.headers.get('content-type') || '';
                let details = '';
                try {
                    if (contentType.includes('application/json')) {
                        const json = await response.json();
                        details = json?.message ? String(json.message) : JSON.stringify(json);
                    } else {
                        details = (await response.text())?.slice(0, 300) || '';
                    }
                } catch (_) {
                    details = '';
                }

                throw new Error(`Erreur HTTP ${response.status}${details ? ' — ' + details : ''}`);
            } catch (err) {
                const msg = err?.name === 'AbortError'
                    ? 'Le serveur met trop de temps à répondre. Réessayez.'
                    : (err?.message || 'Erreur lors de la sauvegarde.');

                modalMessage.textContent = msg;
                modalMessage.style.display = 'block';
            } finally {
                clearTimeout(timeout);
                isSubmitting = false;
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.style.cursor = 'pointer';
                btnCancel.disabled = false;
                modalLoading.style.display = 'none';
            }
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

            // Accessibilité: role="switch" => maintenir aria-checked synchronisé
            const aineSwitch = document.getElementById('aineDansAutreSeaska');
            if (aineSwitch) {
                aineSwitch.setAttribute('aria-checked', aineSwitch.checked ? 'true' : 'false');
                aineSwitch.addEventListener('change', () => {
                    aineSwitch.setAttribute('aria-checked', aineSwitch.checked ? 'true' : 'false');
                });
            }

            // Sync slider <-> input parité
            if (sliderParite) {
                sliderParite.addEventListener('input', () => setParity(sliderParite.value));
            }
            if (inputPariteP1) {
                inputPariteP1.addEventListener('input', () => setParity(inputPariteP1.value));
            }
            // Init values
            setParity(getParity());
        });
    </script>
</x-app-layout>

