<x-app-layout>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    @php
        $isEdit = isset($famille);
        $idFamille = $isEdit ? $famille->idFamille : null;
        
        $defaultRatio = 50;
        // On compte les enfants pour la logique JS
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
        
        <form id="mainForm" method="POST" action="#" class="admin-form">
            @csrf
            
            <h3 class="mb-3 fw-bold">Utilisateurs</h3>
            <div class="row g-4 mb-4">
                
                <div class="col-md-6">
                    <label class="form-label small text-muted fw-bold">Rechercher un utilisateur</label>
                    <input type="text" id="role-search" class="form-control mb-2" placeholder="Tapez pour rechercher...">
                    
                    <div id="available-roles" class="border rounded p-3 bg-white shadow-sm" style="height: auto; overflow-y: visible;">
                        
                        @if($isEdit)
                            @foreach($famille->utilisateurs as $user)
                                <div class="role-item d-flex justify-content-between align-items-center p-2 mb-2 border rounded bg-white hover-shadow" 
                                     style="cursor:pointer; transition: background 0.2s;"
                                     onclick="addRole({{ $user->idUtilisateur }}, '{{ $user->nom }} {{ $user->prenom }}', 'Parent')"
                                     onmouseover="this.style.backgroundColor='#f8f9fa'" 
                                     onmouseout="this.style.backgroundColor='white'">
                                    
                                    <span class="text-dark">{{ $user->nom }} {{ $user->prenom }}</span>
                                    
                                    <div class="d-flex align-items-center text-secondary">
                                        <span class="me-3 small fw-bold">Parent</span>
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-dark">
                                            <circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line>
                                        </svg>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                        
                        {{-- On affiche aussi les enfants ici si besoin (votre code d'origine) --}}
                        @if($isEdit && $famille->enfants->isNotEmpty())
                            @foreach($famille->enfants as $enfant)
                                <div class="role-item d-flex justify-content-between align-items-center p-2 mb-2 border rounded bg-white hover-shadow" 
                                     style="cursor:pointer; transition: background 0.2s;"
                                     {{-- Pas de onclick pour ajouter un enfant comme parent --}}
                                     onmouseover="this.style.backgroundColor='#f8f9fa'" 
                                     onmouseout="this.style.backgroundColor='white'">
                                    <span>{{$enfant->nom}} {{$enfant->prenom}}</span>
                                    <div class="d-flex align-items-center text-secondary">
                                        <span class="me-3 small fw-bold">Enfant</span>
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-dark">
                                            <circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line>
                                        </svg>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-muted fst-italic fw-bold">Aucun enfant enregistré.</div>
                        @endif
                    </div>
                </div>

                <div class="col-md-6 ">
                    <label class="form-label small text-muted mb-2 fw-bold">Utilisateurs sélectionné(s)*</label>
                    <div id="selected-roles" class="border rounded p-3 bg-light" style="height: 245px; overflow-y: auto;">
                        <div class="role-list-empty-message text-muted text-center mt-5">
                            Cliquez sur les utilisateurs à gauche pour les sélectionner.
                        </div>
                    </div>
                </div>
            </div>
            
           

          

            <div id="financial-section" style="display: none;">
              
                <div x-data="{ ratio: {{ $defaultRatio }} }">
                    
                    <div class="d-flex flex-wrap align-items-center">
                          <h5 class="mb-0 fw-bold me-5">Répartition financière</h5>
                          <h1></h1>
                        <span id="label-parent-1" class="fw-bold text-secondary text-nowrap me-3">Parent 1</span>
                        <div class="border rounded px-2 py-2 bg-white d-flex align-items-center shadow-sm" style="width: 220px;">
                            <input type="range" class="form-range" min="0" max="100" x-model="ratio" x-ref="sliderParite" style="--bs-form-range-thumb-bg: orange; accent-color: orange; margin-bottom: 0;">
                        </div>
                        <span id="label-parent-2" class="fw-bold text-secondary text-nowrap ms-3">Parent 2</span>
                        <div class="ms-auto d-flex gap-2">
                            <a href="{{ route('admin.familles.index')}}" class="btn px-3 py-2 fw-bold" style="background:white; border:1px solid orange; color:orange;">Utzi</a>
                            <button type="button" class="btn px-3 py-2 fw-bold" style="background:orange; color:white; border:1px solid orange;" @if($isEdit) onclick="saveParityOnly({{ $idFamille }})" @else onclick="document.getElementById('mainForm').submit()" @endif>Gorde</button>
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

    {{-- MODALES (Inchangées) --}}
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header border-0 pb-0 ps-4 pt-4"><h5 class="modal-title fw-bold fs-4 text-dark">Modifier la parité</h5></div>
                <div class="modal-body ps-4 pe-4 pt-2 text-secondary">Êtes-vous sûr de vouloir modifier la répartition financière de cette famille ?</div>
                <div class="modal-footer border-0 pe-4 pb-4">
                    <button type="button" class="btn px-4 py-2 fw-bold" data-bs-dismiss="modal" style="background: white; border: 1px solid orange; color: orange; border-radius: 6px;">Annuler</button>
                    <button type="button" id="btnConfirmSave" class="btn px-4 py-2 fw-bold text-white" style="background: orange; border: 1px solid orange; border-radius: 6px;">Modifier</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-body text-center p-5">
                    <div class="mb-4 text-success"><svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg></div>
                    <h4 class="fw-bold mb-3">Modification effectuée !</h4>
                    <p class="text-muted mb-4">La répartition financière a été mise à jour avec succès.</p>
                    <button type="button" id="btnSuccessOk" class="btn px-5 py-2 fw-bold text-white" style="background: orange; border: 1px solid orange; border-radius: 6px;">D'accord</button>
                </div>
            </div>
        </div>
    </div>

    {{-- JAVASCRIPT --}}
    <script>
        const selectedRoles = document.getElementById('selected-roles');
        const roleInputs = document.getElementById('role-inputs');
        const financialSection = document.getElementById('financial-section');
        const labelP1 = document.getElementById('label-parent-1');
        const labelP2 = document.getElementById('label-parent-2');
        let pendingData = null;
        const dbRatio = {{ $defaultRatio }}; 
        
        // RECUPERER LE NOMBRE D'ENFANTS DEPUIS PHP
        const nbEnfants = {{ $countEnfants }};

        function checkParityVisibility() {
            const items = selectedRoles.querySelectorAll('.role-item');
            const slider = document.querySelector('[x-ref="sliderParite"]');
            if (items.length > 0) {
                financialSection.style.display = 'block';
                labelP1.innerText = items[0].querySelector('span.fw-bold').innerText;
                if (items.length === 1) {
                    labelP2.innerText = ""; 
                    slider.value = 100; slider.disabled = true; slider.style.cursor = 'not-allowed'; slider.style.opacity = '0.5'; 
                    slider.dispatchEvent(new Event('input')); 
                } else if (items.length === 2) {
                    labelP2.innerText = items[1].querySelector('span.fw-bold').innerText;
                    slider.disabled = false; slider.style.cursor = 'pointer'; slider.style.opacity = '1';
                    slider.value = dbRatio; slider.dispatchEvent(new Event('input')); 
                }
            } else { financialSection.style.display = 'none'; }
        }

        function addRole(id, name, parentLabel) {
            // 🟢 MODIFICATION ICI : EMPÊCHER LA SÉLECTION SI 0 ENFANT
            if (nbEnfants === 0) {
                alert("Impossible de modifier la répartition : cette famille n'a aucun enfant.");
                return;
            }

            // if (selectedRoles.querySelectorAll('.role-item').length >= 2) { alert("Maximum 2 parents."); return; } // Commenté comme dans ton code
            const exists = Array.from(selectedRoles.querySelectorAll('input[name="utilisateurs[][idUtilisateur]"]')).some(input => input.value == id);
            if (exists) return;
            
            const emptyMsg = selectedRoles.querySelector('.role-list-empty-message');
            if (emptyMsg) emptyMsg.remove();

            const div = document.createElement('div');
            div.className = 'role-item d-flex justify-content-between align-items-center p-2 mb-1 border rounded shadow-sm';
            div.style.backgroundColor = 'orange'; div.style.color = 'white'; div.style.cursor = 'pointer';
            
            div.innerHTML = `<span class="fw-bold small">${name}</span><span class="d-flex align-items-center gap-2"><small>${parentLabel}</small><span class="fw-bold fs-6">&times;</span></span>`;
            selectedRoles.appendChild(div);

            const input = document.createElement('input');
            input.type = 'hidden'; input.name = 'utilisateurs[][idUtilisateur]'; input.value = id; div.appendChild(input);

            div.addEventListener('click', () => {
                div.remove();
                if (selectedRoles.children.length === 0) selectedRoles.innerHTML = '<div class="role-list-empty-message text-muted text-center mt-5">Cliquez sur les utilisateurs à gauche pour les sélectionner.</div>';
                checkParityVisibility();
            });
            checkParityVisibility();
        }

        function saveParityOnly(idFamille) {
            const inputs = selectedRoles.querySelectorAll('input[name="utilisateurs[][idUtilisateur]"]');
            if (inputs.length < 1) { alert("Erreur : Sélectionnez au moins 1 parent."); return; }
            const idUtilisateur = inputs[0].value;
            const slider = document.querySelector('[x-ref="sliderParite"]');
            const pariteValue = (inputs.length === 1) ? 100 : slider.value;
            if (!idFamille) { alert("Erreur ID Famille"); return; }

            pendingData = { idFamille: idFamille, idUtilisateur: idUtilisateur, parite: pariteValue };
            const myModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
            myModal.show();
        }

        document.getElementById('btnConfirmSave').addEventListener('click', function() {
            if (!pendingData) return;
            const confirmModal = bootstrap.Modal.getInstance(document.getElementById('confirmationModal'));
            
            fetch("{{ route('admin.lier.updateParite') }}", {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                body: JSON.stringify(pendingData)
            }).then(res => res.json()).then(data => {
                confirmModal.hide();
                const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                successModal.show();
                document.getElementById('btnSuccessOk').addEventListener('click', () => { successModal.hide(); window.location.href = "{{ route('admin.familles.index') }}"; });
            }).catch(err => { console.error(err); confirmModal.hide(); alert("Erreur lors de la mise à jour"); });
        });

        document.addEventListener('DOMContentLoaded', () => { checkParityVisibility(); });
    </script>
</x-app-layout>