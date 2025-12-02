<x-app-layout>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    @php
        // --- LOGIQUE PHP ---
        $isEdit = isset($famille);
        $title = $isEdit ? "Modifier la famille #{$famille->idFamille}" : "Ajouter une famille";
        
        // ID Famille pour l'AJAX
        $idFamille = $isEdit ? $famille->idFamille : null;

        // Préparation des noms pour le slider (si dispo)
        $nomP1 = "Parent 1";
        $nomP2 = "Parent 2";
        $defaultRatio = 50;

        if ($isEdit && $famille->utilisateurs->count() >= 2) {
            $p1 = $famille->utilisateurs->get(0);
            $p2 = $famille->utilisateurs->get(1);
            $nomP1 = $p1->prenom;
            $nomP2 = $p2->prenom;
            
            // Récupération de la parité existante
            if ($p1->pivot && $p1->pivot->parite !== null) {
                $defaultRatio = $p1->pivot->parite;
            }
        }
    @endphp

    <div class="container py-5">
        <h2 class="mb-5 fw-bolder">{{ $title }}</h2>
        
        {{-- Formulaire (Action Store utilisée uniquement pour la création) --}}
        <form id="mainForm" method="POST" action="#" class="admin-form">
            @csrf
            
            {{-- ================================================================== --}}
            {{-- 1. UTILISATEURS --}}
            {{-- ================================================================== --}}
            <h3 class="mb-3 fw-bold">Utilisateurs</h3>
            <div class="row g-4 mb-4">
                
                {{-- COLONNE GAUCHE (Recherche) --}}
                <div class="col-md-6">
                    <label class="form-label small text-muted">Utilisateurs disponibles</label>
                    <input type="text" id="role-search" class="form-control mb-2" placeholder="Tapez pour rechercher...">
                    
                    <div id="available-roles" class="border rounded p-3 bg-white shadow-sm" style="height: 200px; overflow-y:auto;">
                        {{-- 🟢 EN MODE EDIT : On affiche les parents existants ICI à gauche --}}
                        @if($isEdit)
                            @foreach($famille->utilisateurs as $user)
                                <div class="role-item d-flex justify-content-between align-items-center p-2 mb-1 border rounded" 
                                     style="cursor:pointer; border-left: 4px solid #f97316 !important;"
                                     onclick="addRole({{ $user->idUtilisateur }}, '{{ $user->nom }} {{ $user->prenom }}', 'Parent')">
                                    <span>{{ $user->nom }} {{ $user->prenom }}</span>
                                    <small class="text-muted fw-bold">Ajouter</small>
                                </div>
                            @endforeach
                        @endif
                        <div id="search-results-placeholder" class="text-muted text-center mt-3 small fst-italic">
                            Résultats de recherche...
                        </div>
                    </div>
                </div>

                {{-- COLONNE DROITE (Sélectionnés) --}}
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-2">Utilisateurs sélectionnés</label>
                    <div id="selected-roles" class="border rounded p-3 bg-light" style="height: 245px;">
                        <div class="role-list-empty-message text-muted text-center mt-5">
                            Cliquez sur les utilisateurs à gauche pour les sélectionner.
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Zone pour les inputs cachés --}}
            <div id="role-inputs"></div>


            {{-- ================================================================== --}}
            {{-- 2. ENFANTS --}}
            {{-- ================================================================== --}}
            <h3 class="mb-3 fw-bold mt-5">Enfants</h3>
            <div class="card border p-4 mb-5 shadow-sm bg-white">
                @if($isEdit && $famille->enfants->isNotEmpty())
                    @foreach($famille->enfants as $enfant)
                        <div class="row g-2 mb-2 align-items-center">
                            <div class="col-md-4"><input type="text" class="form-control" value="{{ $enfant->nom }}" disabled></div>
                            <div class="col-md-4"><input type="text" class="form-control" value="{{ $enfant->prenom }}" disabled></div>
                            <div class="col-md-4"><input type="text" class="form-control" value="{{ $enfant->dateN }}" disabled></div>
                        </div>
                    @endforeach
                @else
                    <div class="text-muted fst-italic">Aucun enfant enregistré.</div>
                @endif
            </div>


            <hr class="my-5"/>

            {{-- ================================================================== --}}
            {{-- 3. RÉPARTITION FINANCIÈRE (Masquée tant que < 2 parents) --}}
            {{-- ================================================================== --}}
            <div id="financial-section" style="display: none;">
                <h3 class="mb-4 fw-bold">Répartition financière</h3>
                
                <div x-data="{ ratio: {{ $defaultRatio }} }">
                    <div class="d-flex flex-wrap align-items-center">
                        
                        {{-- Label Parent 1 --}}
                        <span id="label-parent-1" class="fw-bold text-secondary text-nowrap me-3">{{ $nomP1 }}</span>

                        {{-- Slider --}}
                        <div class="border rounded px-2 py-2 bg-white d-flex align-items-center shadow-sm" style="width: 220px;">
                            <input type="range" class="form-range" min="0" max="100" x-model="ratio" x-ref="sliderParite" style="accent-color: #f97316; margin-bottom: 0;">
                        </div>

                        {{-- Label Parent 2 --}}
                        <span id="label-parent-2" class="fw-bold text-secondary text-nowrap ms-3">{{ $nomP2 }}</span>

                        {{-- Boutons --}}
                        <div class="ms-auto d-flex gap-2">
                            <a href="{{ route('admin.familles.index')}}" class="btn px-3 py-2 fw-bold" style="background:white; border:1px solid orange; color:orange;">Utzi</a>
                            
                            {{-- Bouton Intelligent --}}
                            <button type="button" 
                                    class="btn px-3 py-2 fw-bold" 
                                    style="background:orange; color:white; border:1px solid orange;"
                                    @if($isEdit)
                                        onclick="saveParityOnly({{ $idFamille }})"
                                    @else
                                        onclick="document.getElementById('mainForm').submit()"
                                    @endif
                                    >
                                Gorde
                            </button>
                        </div>
                    </div>

                    {{-- Texte dynamique --}}
                    <div class="mt-2 d-flex w-100">
                        <div style="padding-left: 330px;"> 
                            <span class="fw-bolder fs-5 text-dark">
                                <span x-text="ratio"></span> / <span x-text="100 - ratio"></span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>

    {{-- ===================== JAVASCRIPT ===================== --}}

    <script>
        const selectedRoles = document.getElementById('selected-roles');
        const roleInputs = document.getElementById('role-inputs');
        const financialSection = document.getElementById('financial-section');
        const labelP1 = document.getElementById('label-parent-1');
        const labelP2 = document.getElementById('label-parent-2');

        // Gérer l'affichage de la section financière
        function checkParityVisibility() {
            const items = selectedRoles.querySelectorAll('.role-item');
            if (items.length === 2) {
                financialSection.style.display = 'block';
                // Mise à jour visuelle des noms
                labelP1.innerText = items[0].querySelector('span.fw-bold').innerText;
                labelP2.innerText = items[1].querySelector('span.fw-bold').innerText;
            } else {
                financialSection.style.display = 'none';
            }
        }

        // Ajouter un utilisateur à droite
        function addRole(id, name, parentLabel) {
            if (selectedRoles.querySelectorAll('.role-item').length >= 2) {
                alert("Maximum 2 parents.");
                return;
            }
            if (selectedRoles.querySelector(`[data-role-id='${id}']`)) return;
            
            const emptyMsg = selectedRoles.querySelector('.role-list-empty-message');
            if (emptyMsg) emptyMsg.remove();

            const div = document.createElement('div');
            div.className = 'role-item d-flex justify-content-between align-items-center p-2 mb-1 border rounded shadow-sm';
            div.dataset.roleId = id;
            div.style.backgroundColor = '#f97316'; 
            div.style.color = 'white';
            div.style.cursor = 'pointer';
            
            div.innerHTML = `
                <span class="fw-bold small">${name}</span>
                <span class="d-flex align-items-center gap-2">
                    <small>${parentLabel}</small>
                    <span class="fw-bold fs-6">&times;</span>
                </span>
            `;

            selectedRoles.appendChild(div);

            // Input caché (Important pour l'AJAX : on le cherche par name="utilisateurs[][idUtilisateur]")
            const input = document.createElement('input');
            input.type = 'hidden'; 
            input.name = 'utilisateurs[][idUtilisateur]'; 
            input.value = id; 
            roleInputs.appendChild(input);

            div.addEventListener('click', () => {
                div.remove();
                input.remove();
                if (selectedRoles.children.length === 0) {
                    selectedRoles.innerHTML = '<div class="role-list-empty-message text-muted text-center mt-5">Cliquez sur les utilisateurs à gauche.</div>';
                }
                checkParityVisibility();
            });

            checkParityVisibility();
        }
    </script>

    {{-- SCRIPT AJAX PARITÉ --}}
    <script>
        function saveParityOnly(idFamille) {
            // 1. On cherche les inputs des utilisateurs sélectionnés
            const inputs = document.querySelectorAll('input[name="utilisateurs[][idUtilisateur]"]');
            
            if (inputs.length < 2) {
                alert("Erreur : Veuillez sélectionner 2 parents pour modifier la répartition.");
                return;
            }

            // 2. On prend le PREMIER ID trouvé comme "Parent 1"
            const idUtilisateur = inputs[0].value;
            const slider = document.querySelector('[x-ref="sliderParite"]');

            if (!idFamille) { alert("Erreur ID Famille"); return; }

            // 3. Envoi AJAX
            fetch("{{ route('admin.lier.updateParite') }}", {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    idFamille: idFamille,
                    idUtilisateur: idUtilisateur, // Parent 1
                    parite: slider.value          // Sa part (ex: 70)
                })
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                window.location.href = "{{ route('admin.familles.index') }}";
            })
            .catch(err => {
                console.error(err);
                alert("Erreur technique lors de la sauvegarde.");
            });
        }
    </script>
</x-app-layout>