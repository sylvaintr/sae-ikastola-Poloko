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
                
                {{-- COLONNE GAUCHE --}}
                <div class="col-md-6">
                    <label class="form-label small text-muted fw-bold">Rechercher un utilisateur</label>
                    {{-- Filtrage simple via JS pour ne pas changer le design --}}
                    <input type="text" id="role-search" class="form-control mb-2" placeholder="Tapez pour rechercher..." onkeyup="filterList(this.value)">
                    
                    <div id="available-roles" class="border rounded p-3 bg-white shadow-sm" style="height: auto; max-height: 500px; overflow-y: auto;">
                        
                        {{-- CAS 1 : MODE MODIFICATION (Affiche les membres actuels) --}}
                        @if($isEdit)
                           
                            @foreach($famille->utilisateurs as $user)
                                <div class="role-item d-flex justify-content-between align-items-center p-2 mb-2 border rounded bg-white hover-shadow" 
                                     style="cursor:pointer; transition: background 0.2s;"
                                     onclick="addRole({{ $user->idUtilisateur }}, '{{ $user->nom }} {{ $user->prenom }}', 'Parent')"
                                     onmouseover="this.style.backgroundColor='#f8f9fa'" 
                                     onmouseout="this.style.backgroundColor='white'">
                                    <span class="text-dark item-name">{{ $user->nom }} {{ $user->prenom }}</span>
                                    <div class="d-flex align-items-center text-secondary">
                                        <span class="me-3 small fw-bold">Parent</span>
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-dark"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                                    </div>
                                </div>
                            @endforeach

                            @if($famille->enfants->isNotEmpty())
                                
                               
                                @foreach($famille->enfants as $enfant)
                                    <div class="role-item d-flex justify-content-between align-items-center p-2 mb-2 border rounded bg-white hover-shadow" 
                                         style="cursor:default;">
                                        <span class="text-dark item-name">{{ $enfant->nom }} {{ $enfant->prenom }}</span>
                                        <div class="d-flex align-items-center text-secondary">
                                            <span class="me-3 small fw-bold">Enfant</span>
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-dark"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                        {{-- CAS 2 : MODE CRÉATION (Affiche TOUT le monde dans le même bloc) --}}
                        @else
                            {{-- Liste des Parents Disponibles --}}
                            @if(isset($tousUtilisateurs))
                                @foreach($tousUtilisateurs as $user)
                                    <div class="role-item d-flex justify-content-between align-items-center p-2 mb-2 border rounded bg-white hover-shadow" 
                                         style="cursor:pointer; transition: background 0.2s;"
                                         onclick="addRole({{ $user->idUtilisateur }}, '{{ $user->nom }} {{ $user->prenom }}', 'Parent')"
                                         onmouseover="this.style.backgroundColor='#f8f9fa'" 
                                         onmouseout="this.style.backgroundColor='white'">
                                        <span class="text-dark item-name">{{ $user->nom }} {{ $user->prenom }}</span>
                                        <div class="d-flex align-items-center text-secondary">
                                            <span class="me-3 small fw-bold">Parent</span>
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-dark"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            {{-- Liste des Enfants Disponibles (Juste en dessous, sans grand titre) --}}
                            @if(isset($tousEnfants))
                                @foreach($tousEnfants as $enfant)
                                    {{-- Note: J'utilise une couleur de bordure différente (Bleu) pour distinguer visuellement --}}
                                    <div class="role-item d-flex justify-content-between align-items-center p-2 mb-2 border rounded bg-white hover-shadow" 
                                         style="cursor:pointer; transition: background 0.2s; border-left: 4px solid #0dcaf0 !important;"
                                         onclick="addChild({{ $enfant->idEnfant }}, '{{ $enfant->nom }} {{ $enfant->prenom }}')"
                                         onmouseover="this.style.backgroundColor='#f8f9fa'" 
                                         onmouseout="this.style.backgroundColor='white'">
                                        <span class="text-dark item-name">{{ $enfant->nom }} {{ $enfant->prenom }}</span>
                                        <div class="d-flex align-items-center text-secondary">
                                            <span class="me-3 small fw-bold">Enfant</span>
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-dark"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        @endif
                        
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label small text-muted mb-2 fw-bold">Utilisateurs sélectionné(s)*</label>
                    <div id="selected-roles" class="border rounded p-3 bg-light" style="height: 245px; overflow-y: auto;">
                        <div class="role-list-empty-message text-muted text-center mt-5">
                            Cliquez sur les utilisateurs à gauche pour les sélectionner.
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="role-inputs"></div>

            {{-- 3. RÉPARTITION FINANCIÈRE --}}
            <div id="financial-section" style="display: none;">
                
                <div x-data="{ ratio: {{ $defaultRatio }} }">
                 
                    <div class="d-flex flex-wrap align-items-center">
                            <h5 class="mb-0 fw-bold me-5">Répartition financière</h5>
                        <span id="label-parent-1" class="fw-bold text-secondary text-nowrap me-3">Parent 1</span>
                        <div class="border rounded px-2 py-2 bg-white d-flex align-items-center shadow-sm" style="width: 220px;">
                            <input type="range" class="form-range" min="0" max="100" x-model="ratio" x-ref="sliderParite" style="--bs-form-range-thumb-bg: orange; accent-color: orange; margin-bottom: 0;">
                        </div>
                        <span id="label-parent-2" class="fw-bold text-secondary text-nowrap ms-3">Parent 2</span>
                        <div class="ms-auto d-flex gap-2">
                            <a href="{{ route('admin.familles.index')}}" class="btn px-3 py-2 fw-bold" style="background:white; border:1px solid orange; color:orange;">Utzi</a>
                            <button type="button" class="btn px-3 py-2 fw-bold" style="background:orange; color:white; border:1px solid orange;" 
                                    @if($isEdit) onclick="saveParityOnly({{ $idFamille }})" @else onclick="createFamily()" @endif>
                                Gorde
                            </button>
                        </div>
                    </div>
                    <div class="mt-2 d-flex w-100">
                        <div style="padding-left: 330px;"> 
                            <span class="fw-bolder fs-5 text-dark"><span x-text="ratio"></span> / <span x-text="100 - ratio"></span></span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- MODALES --}}
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header border-0 pb-0 ps-4 pt-4"><h5 class="modal-title fw-bold fs-4 text-dark">Modifier la parité</h5></div>
                <div class="modal-body ps-4 pe-4 pt-2 text-secondary">Êtes-vous sûr de vouloir modifier la répartition financière de cette famille ?</div>
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
                    <div class="mb-4 text-success"><svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg></div>
                    <h4 class="fw-bold mb-3">Succès !</h4>
                    <button type="button" id="btnSuccessOk" class="btn px-5 py-2 fw-bold text-white" style="background: orange; border: 1px solid orange; border-radius: 6px;">OK</button>
                </div>
            </div>
        </div>
    </div>

    {{-- JAVASCRIPT --}}
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

        // Fonction de filtrage simple pour la liste de gauche
        function filterList(val) {
            const filter = val.toLowerCase();
            const items = document.querySelectorAll('#available-roles .role-item');
            items.forEach(item => {
                const text = item.innerText.toLowerCase();
                item.style.display = text.includes(filter) ? 'flex' : 'none';
            });
        }

        function checkParityVisibility() {
            // On compte UNIQUEMENT les parents (user-id)
            const parentInputs = selectedRoles.querySelectorAll('input.user-id');
            const slider = document.querySelector('[x-ref="sliderParite"]');
            
            if (parentInputs.length > 0) {
                financialSection.style.display = 'block';
                const name1 = parentInputs[0].closest('.role-item').querySelector('.item-name').innerText.split(' ')[0];
                labelP1.innerText = name1;

                if (parentInputs.length === 1) {
                    labelP2.innerText = ""; labelP2.style.display = 'none';
                    slider.value = 100; slider.disabled = true; slider.style.cursor = 'not-allowed'; slider.style.opacity = '0.5'; 
                    slider.dispatchEvent(new Event('input')); 
                } else if (parentInputs.length === 2) {
                    const name2 = parentInputs[1].closest('.role-item').querySelector('.item-name').innerText.split(' ')[0];
                    labelP2.innerText = name2; labelP2.style.display = 'inline';
                    slider.disabled = false; slider.style.cursor = 'pointer'; slider.style.opacity = '1';
                    if(isEditMode) slider.value = dbRatio; 
                    slider.dispatchEvent(new Event('input')); 
                }
            } else { financialSection.style.display = 'none'; }
        }

        // --- 1. AJOUTER UN PARENT (ORANGE) ---
        function addRole(id, name, parentLabel) {
            if (isEditMode && nbEnfantsInitial === 0) { alert("Impossible de modifier la répartition : cette famille n'a aucun enfant."); return; }
            if (selectedRoles.querySelectorAll('input.user-id').length >= 2) { return; } 
            if (Array.from(selectedRoles.querySelectorAll('input.user-id')).some(i => i.value == id)) return;
            
            const emptyMsg = selectedRoles.querySelector('.role-list-empty-message');
            if (emptyMsg) emptyMsg.remove();

            const div = document.createElement('div');
            div.className = 'role-item d-flex justify-content-between align-items-center p-2 mb-1 border rounded shadow-sm';
            div.style.backgroundColor = 'orange'; div.style.color = 'white'; div.style.cursor = 'pointer';
            
            div.innerHTML = `
                <span class="fw-bold small item-name">${name}</span>
                <span class="d-flex align-items-center gap-2"><small>Parent</small><span class="fw-bold fs-6">&times;</span></span>
                <input type="hidden" class="user-id" value="${id}">
            `;
            selectedRoles.appendChild(div);
            div.addEventListener('click', () => { div.remove(); if (selectedRoles.children.length === 0) selectedRoles.innerHTML = '<div class="role-list-empty-message text-muted text-center mt-5">Cliquez sur les utilisateurs à gauche pour les sélectionner.</div>'; checkParityVisibility(); });
            checkParityVisibility();
        }

        // --- 2. AJOUTER UN ENFANT (BLEU/CYAN - NOUVEAU) ---
        function addChild(id, name) {
            // Vérif doublon
            if (Array.from(selectedRoles.querySelectorAll('input.child-id')).some(i => i.value == id)) return;

            const emptyMsg = selectedRoles.querySelector('.role-list-empty-message');
            if (emptyMsg) emptyMsg.remove();

            const div = document.createElement('div');
            // Style différent (Bleu) pour distinguer visuellement
            div.className = 'role-item d-flex justify-content-between align-items-center p-2 mb-1 border rounded shadow-sm bg-white border-start border-4 border-info';
            div.style.cursor = 'pointer';
            
            div.innerHTML = `
                <span class="fw-bold small item-name text-dark">${name}</span>
                <span class="d-flex align-items-center gap-2 text-info"><small>Enfant</small><span class="fw-bold fs-6 text-dark">&times;</span></span>
                <input type="hidden" class="child-id" value="${id}">
            `;
            selectedRoles.appendChild(div);
            div.addEventListener('click', () => { div.remove(); if (selectedRoles.children.length === 0) selectedRoles.innerHTML = '<div class="role-list-empty-message text-muted text-center mt-5">Cliquez sur les utilisateurs à gauche pour les sélectionner.</div>'; });
        }

        // --- 3. CRÉATION FAMILLE (Envoi IDs Parents + Enfants) ---
        function createFamily() {
            const parentInputs = selectedRoles.querySelectorAll('input.user-id');
            const childInputs = selectedRoles.querySelectorAll('input.child-id');

            if (parentInputs.length === 0) { alert('Il faut au moins 1 parent.'); return; }
            if (childInputs.length === 0) { alert('Il faut au moins 1 enfant.'); return; }

            const slider = document.querySelector('[x-ref="sliderParite"]');
            
            // Parents (ID + Parité)
            const utilisateursData = [];
            parentInputs.forEach((input, index) => {
                let p = 100;
                if (parentInputs.length === 2) { p = (index === 0) ? slider.value : (100 - slider.value); }
                utilisateursData.push({ idUtilisateur: input.value, parite: p });
            });

            // Enfants (Juste ID pour lier l'existant)
            const enfantsData = [];
            childInputs.forEach(input => {
                enfantsData.push({ idEnfant: input.value });
            });

            pendingData = { utilisateurs: utilisateursData, enfants: enfantsData };
            isCreateMode = true;
            new bootstrap.Modal(document.getElementById('confirmationModal')).show();
        }

        // --- 4. MODIFICATION PARITÉ ---
        function saveParityOnly(idFamille) {
            const inputs = selectedRoles.querySelectorAll('input.user-id');
            if (inputs.length < 1) { alert("Erreur."); return; }
            const idUtilisateur = inputs[0].value;
            const slider = document.querySelector('[x-ref="sliderParite"]');
            const pariteValue = (inputs.length === 1) ? 100 : slider.value;

            pendingData = { idFamille: idFamille, idUtilisateur: idUtilisateur, parite: pariteValue };
            isCreateMode = false;
            new bootstrap.Modal(document.getElementById('confirmationModal')).show();
        }

        // --- ENVOI AJAX ---
        document.getElementById('btnConfirmSave').addEventListener('click', function() {
            if (!pendingData) return;
            const confirmModal = bootstrap.Modal.getInstance(document.getElementById('confirmationModal'));
            const url = isCreateMode ? "{{ route('admin.familles.store') }}" : "{{ route('admin.lier.updateParite') }}";
            const method = isCreateMode ? 'POST' : 'PUT';

            fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                body: JSON.stringify(pendingData)
            }).then(res => { if(!res.ok) throw new Error('Erreur'); return res.json(); })
            .then(data => {
                confirmModal.hide();
                new bootstrap.Modal(document.getElementById('successModal')).show();
                document.getElementById('btnSuccessOk').addEventListener('click', () => { window.location.href = "{{ route('admin.familles.index') }}"; });
            }).catch(err => { console.error(err); confirmModal.hide(); alert("Erreur opération."); });
        });

        document.addEventListener('DOMContentLoaded', () => { checkParityVisibility(); });
    </script>
</x-app-layout>