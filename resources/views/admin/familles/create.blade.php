<x-app-layout>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

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
        <h2 class="mb-5 fw-bolder">{{ $isEdit ? "Modifier la famille #{$famille->idFamille}" : "Gestion de la famille" }}</h2>

        <form id="mainForm" onsubmit="return false;" class="admin-form">
            @csrf

            <h3 class="mb-3 fw-bold">Utilisateurs</h3>
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label for="role-search" class="form-label small text-muted fw-bold">Rechercher un utilisateur</label>
                    <input type="text" id="role-search" class="form-control mb-2" placeholder="Tapez pour rechercher..." onkeyup="searchUsersAJAX(this.value)">

                    <div id="available-roles" class="border rounded p-3 bg-white shadow-sm" style="height: auto; max-height: 500px; overflow-y: auto;">
                        @if($isEdit)
                            @foreach($famille->utilisateurs as $user)
                                <button type="button" class="role-item d-flex align-items-center p-2 mb-2 border rounded bg-white hover-shadow w-100 text-start" onclick="addRole({{ $user->idUtilisateur }}, '{{ $user->nom }} {{ $user->prenom }}')">
                                    <span class="text-dark item-name">{{ $user->nom }} {{ $user->prenom }}</span>
                                    <div class="ms-auto d-flex align-items-center text-secondary">
                                        <span class="me-3 small fw-bold">Parent</span>
                                        <i class="bi bi-plus-circle text-dark fs-5"></i>
                                    </div>
                                </button>
                            @endforeach
                            @foreach($famille->enfants as $enfant)
                                <button type="button" class="role-item d-flex align-items-center p-2 mb-2 border rounded bg-white hover-shadow w-100 text-start" style="border-left: 4px solid #0dcaf0 !important;" onclick="addChild({{ $enfant->idEnfant }}, '{{ $enfant->nom }} {{ $enfant->prenom }}')">
                                    <span class="text-dark item-name">{{ $enfant->nom }} {{ $enfant->prenom }}</span>
                                    <div class="ms-auto d-flex align-items-center text-secondary">
                                        <span class="me-3 small fw-bold">Enfant</span>
                                        <i class="bi bi-plus-circle text-dark fs-5"></i>
                                    </div>
                                </button>
                            @endforeach
                        @else
                            <div id="parents-list">
                                @if(isset($tousUtilisateurs))
                                    @foreach($tousUtilisateurs as $user)
                                        <button type="button" class="role-item d-flex align-items-center p-2 mb-2 border rounded bg-white hover-shadow w-100 text-start" onclick="addRole({{ $user->idUtilisateur }}, '{{ $user->nom }} {{ $user->prenom }}')">
                                            <span class="text-dark item-name">{{ $user->nom }} {{ $user->prenom }}</span>
                                            <div class="ms-auto d-flex align-items-center text-secondary"><span class="me-3 small fw-bold">Parent</span><i class="bi bi-plus-circle text-dark fs-5"></i></div>
                                        </button>
                                    @endforeach
                                @endif
                            </div>
                            @if(isset($tousEnfants))
                                @foreach($tousEnfants as $enfant)
                                    <button type="button" class="role-item d-flex align-items-center p-2 mb-2 border rounded bg-white hover-shadow w-100 text-start" style="border-left: 4px solid #0dcaf0 !important;" onclick="addChild({{ $enfant->idEnfant }}, '{{ $enfant->nom }} {{ $enfant->prenom }}')">
                                        <span class="text-dark item-name">{{ $enfant->nom }} {{ $enfant->prenom }}</span>
                                        <div class="ms-auto d-flex align-items-center text-secondary"><span class="me-3 small fw-bold">Enfant</span><i class="bi bi-plus-circle text-dark fs-5"></i></div>
                                    </button>
                                @endforeach
                            @endif
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <h6 class="form-label small text-muted mb-2 fw-bold">Utilisateurs sélectionné(s)*</h6>
                    <div id="selected-roles" class="border rounded p-3 bg-light" style="height: 245px; overflow-y: auto;">
                        <div class="role-list-empty-message text-muted text-center mt-5">Cliquez à gauche pour sélectionner.</div>
                    </div>
                </div>
            </div>

            <div id="financial-section" style="display: none;">
                <div x-data="{ ratio: {{ $defaultRatio }} }">
                    <div class="d-flex flex-wrap align-items-center">
                        <h5 class="mb-0 fw-bold me-5">Répartition financière</h5>
                        <span id="label-parent-1" class="fw-bold text-secondary text-nowrap me-3">Parent 1</span>
                        <div class="border rounded px-2 py-2 bg-white d-flex align-items-center shadow-sm" style="width: 220px;">
                            <input type="range" id="range-parite" class="form-range" min="0" max="100" x-model="ratio" x-ref="sliderParite" style="accent-color: orange;">
                        </div>
                        <span id="label-parent-2" class="fw-bold text-secondary text-nowrap ms-3">Parent 2</span>
                        <div class="ms-auto d-flex gap-2">
                            <a href="{{ route('admin.familles.index')}}" class="btn px-3 py-2 fw-bold" style="background:white; border:1px solid orange; color:orange;">Utzi</a>
                            <button type="button" class="btn px-3 py-2 fw-bold" style="background:orange; color:white; border:1px solid orange;" @if($isEdit) onclick="saveParityOnly({{ $idFamille }})" @else onclick="createFamily()" @endif>Gorde</button>
                        </div>
                    </div>
                    <div class="mt-2 d-flex" style="padding-left: 330px;">
                        <span class="fw-bolder fs-5 text-dark">
                            <span x-text="ratio">{{ $defaultRatio }}</span> / <span x-text="100 - ratio">{{ 100 - $defaultRatio }}</span>
                        </span>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- MODALS --}}
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header border-0 pb-0 ps-4 pt-4"><h5 id="modalTitle" class="modal-title fw-bold fs-4 text-dark">Confirmation</h5></div>
                <div id="modalMessage" class="modal-body ps-4 pe-4 pt-2 text-secondary">Action ?</div>
                <div class="modal-footer border-0 pe-4 pb-4">
                    <button type="button" class="btn px-4 py-2 fw-bold" data-bs-dismiss="modal" style="background: white; border: 1px solid orange; color: orange; border-radius: 6px;">Annuler</button>
                    <button type="button" id="btnConfirmSave" class="btn px-4 py-2 fw-bold text-white" style="background: orange; border: 1px solid orange; border-radius: 6px;">Valider</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-body text-center p-5">
                    <div class="mb-4 text-success"><svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg></div>
                    <h4 class="fw-bold mb-3">Succès !</h4>
                    <button type="button" id="btnSuccessOk" class="btn px-5 py-2 fw-bold text-white" style="background: orange; border: 1px solid orange; border-radius: 6px;">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const selectedRoles = document.getElementById('selected-roles');
        const financialSection = document.getElementById('financial-section');
        const labelP1 = document.getElementById('label-parent-1');
        const labelP2 = document.getElementById('label-parent-2');
        let pendingData = null;
        let isCreateMode = false;

        const dbRatio = {{ $defaultRatio }};
        const nbEnfantsInitial = {{ $countEnfants }};
        const isEditMode = {{ $isEdit ? 'true' : 'false' }};

        function escapeHtml(text) {
            if (!text) return '';
            const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
            return String(text).replace(/[&<>"']/g, m => map[m]);
        }

        function searchUsersAJAX(query) {
            fetch(`/api/search/users?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    const container = document.getElementById('parents-list');
                    container.innerHTML = data.length === 0 ? '<div class="text-muted small fst-italic p-2">Aucun trouvé.</div>' : '';
                    data.forEach(user => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'role-item d-flex align-items-center p-2 mb-2 border rounded bg-white hover-shadow w-100 text-start';
                        btn.onclick = () => addRole(user.idUtilisateur, user.nom + ' ' + user.prenom);
                        btn.innerHTML = `<span class="text-dark item-name">${escapeHtml(user.nom + ' ' + user.prenom)}</span><div class="ms-auto d-flex align-items-center text-secondary"><span class="me-3 small fw-bold">Parent</span><i class="bi bi-plus-circle text-dark fs-5"></i></div>`;
                        container.appendChild(btn);
                    });
                });
        }

        function checkParityVisibility() {
            const parentInputs = selectedRoles.querySelectorAll('input.user-id');
            const slider = document.querySelector('[x-ref="sliderParite"]');
            const alpineData = slider ? Alpine.$data(slider.closest('[x-data]')) : null;

            if (parentInputs.length > 0) {
                financialSection.style.display = 'block';
                labelP1.innerText = parentInputs[0].closest('.role-item').querySelector('.item-name').innerText.split(' ')[0];

                if (parentInputs.length === 1) {
                    labelP2.style.display = 'none';
                    slider.value = 100;
                    if(alpineData) alpineData.ratio = 100;
                    slider.disabled = true; slider.style.opacity = '0.5';
                } else {
                    labelP2.innerText = parentInputs[1].closest('.role-item').querySelector('.item-name').innerText.split(' ')[0];
                    labelP2.style.display = 'inline';
                    slider.disabled = false; slider.style.opacity = '1';
                    const targetVal = isEditMode ? dbRatio : 50;
                    slider.value = targetVal;
                    if(alpineData) alpineData.ratio = targetVal;
                }
                slider.dispatchEvent(new Event('input'));
            } else { financialSection.style.display = 'none'; }
        }

        function addRole(id, name) {
            if (isEditMode && nbEnfantsInitial === 0) { alert("Action impossible : pas d'enfants."); return; }
            if (selectedRoles.querySelectorAll('input.user-id').length >= 2) return;
            if (Array.from(selectedRoles.querySelectorAll('input.user-id')).some(i => i.value == id)) return;

            clearEmptyMsg();
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'role-item d-flex align-items-center p-2 mb-1 border rounded shadow-sm w-100 text-start text-white';
            btn.style.backgroundColor = 'orange';
            btn.innerHTML = `
                <span class="fw-bold small item-name">${escapeHtml(name)}</span>
                <div class="ms-auto d-flex align-items-center gap-2">
                    <small>Parent</small><b class="fs-5">&times;</b>
                </div>
                <input type="hidden" class="user-id" value="${id}">`;
            
            btn.onclick = () => { btn.remove(); checkEmpty(); checkParityVisibility(); };
            selectedRoles.appendChild(btn);
            checkParityVisibility();
        }

        function addChild(id, name) {
            if (Array.from(selectedRoles.querySelectorAll('input.child-id')).some(i => i.value == id)) return;
            clearEmptyMsg();
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'role-item d-flex align-items-center p-2 mb-1 border rounded shadow-sm bg-white border-start border-4 border-info w-100 text-start';
            btn.innerHTML = `
                <span class="fw-bold small item-name text-dark">${escapeHtml(name)}</span>
                <div class="ms-auto d-flex align-items-center gap-2 text-info">
                    <small>Enfant</small><b class="text-dark fs-5">&times;</b>
                </div>
                <input type="hidden" class="child-id" value="${id}">`;
            
            btn.onclick = () => { btn.remove(); checkEmpty(); };
            selectedRoles.appendChild(btn);
        }

        function clearEmptyMsg() { const m = selectedRoles.querySelector('.role-list-empty-message'); if(m) m.remove(); }
        function checkEmpty() { if (selectedRoles.querySelectorAll('.role-item').length === 0) selectedRoles.innerHTML = '<div class="role-list-empty-message text-muted text-center mt-5">Cliquez à gauche pour sélectionner.</div>'; }

        function createFamily() {
            const parents = selectedRoles.querySelectorAll('input.user-id');
            const children = selectedRoles.querySelectorAll('input.child-id');
            if (!parents.length || !children.length) { alert('Sélectionnez au moins 1 parent et 1 enfant.'); return; }
            const slider = document.querySelector('[x-ref="sliderParite"]');
            pendingData = {
                utilisateurs: Array.from(parents).map((p, i) => ({ idUtilisateur: p.value, parite: parents.length === 2 ? (i === 0 ? slider.value : 100 - slider.value) : 100 })),
                enfants: Array.from(children).map(c => ({ idEnfant: c.value }))
            };
            isCreateMode = true;
            showConfirm("Créer la famille", "Voulez-vous créer cette famille ?");
        }

        function saveParityOnly(id) {
            const slider = document.querySelector('[x-ref="sliderParite"]');
            pendingData = { idFamille: id, idUtilisateur: selectedRoles.querySelector('input.user-id').value, parite: slider.value };
            isCreateMode = false;
            showConfirm("Modifier la parité", "Confirmer la nouvelle répartition ?");
        }

        function showConfirm(title, msg) {
            document.getElementById('modalTitle').innerText = title;
            document.getElementById('modalMessage').innerText = msg;
            new bootstrap.Modal(document.getElementById('confirmationModal')).show();
        }

        document.getElementById('btnConfirmSave').onclick = function() {
            const url = isCreateMode ? "{{ route('admin.familles.store') }}" : "{{ route('admin.lier.updateParite') }}";
            fetch(url, {
                method: isCreateMode ? 'POST' : 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(pendingData)
            }).then(() => {
                bootstrap.Modal.getInstance(document.getElementById('confirmationModal')).hide();
                new bootstrap.Modal(document.getElementById('successModal')).show();
                document.getElementById('btnSuccessOk').onclick = () => window.location.href = "{{ route('admin.familles.index') }}";
            });
        };

        document.addEventListener('DOMContentLoaded', () => checkParityVisibility());
    </script>
</x-app-layout>

