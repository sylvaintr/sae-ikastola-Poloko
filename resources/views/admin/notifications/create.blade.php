<x-app-layout>
    <div class="container py-5">
        <div class="mb-5">
            <h2 class="fw-bold mb-0">Jakinarazpenak</h2>
            <small class="text-muted">Créer une règle de notification automatique</small>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                
                {{-- 1. ACTION DU FORMULAIRE CORRIGÉE --}}
                <form method="POST" action="{{ route('admin.notifications.store') }}" class="admin-form" id="notification-form">
                    @csrf

                    {{-- Section 1 : Paramètres Généraux --}}
                    <div class="row g-4 mb-4 align-items-end">
                        <div class="col-md-6">
                            <label for="title" class="form-label fw-bold">Titre de la règle</label>
                            <input type="text" id="title" name="title" class="form-control" placeholder="Ex: Rappel Événements" required>
                        </div>

                        <div class="col-md-2">
                            <label for="recurrence" class="form-label fw-bold">Récurrence</label>
                            <input type="number" id="recurrence" name="recurrence_days" class="form-control" placeholder="Jours">
                        </div>

                        <div class="col-md-2">
                            <label for="reminder" class="form-label fw-bold">Rappel (J-?)</label>
                            <input type="number" id="reminder" name="reminder_days" class="form-control" placeholder="Jours avant" required>
                        </div>

                        <div class="col-md-2 text-center">
                            <label class="form-label fw-bold d-block mb-2">Activé</label>
                            <div class="form-check form-switch d-flex justify-content-center">
                                <input class="form-check-input" type="checkbox" id="isActive" name="is_active" checked 
                                       style="width: 3rem; height: 1.5rem; cursor: pointer;">
                            </div>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label for="description" class="form-label fw-bold">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="3" style="resize: none;" placeholder="Description optionnelle..."></textarea>
                    </div>

                    <hr class="text-muted my-4">

                    {{-- Section 2 : Sélection du Module (Super-Règles) --}}
                    <div class="mb-3">
                        <h4 class="fw-bold mb-0">Cible de la notification</h4>
                        <small class="text-muted">Choisissez quel type de données surveiller</small>
                    </div>

                    <div class="row g-4">
                        {{-- Colonne Gauche : Les Boutons de Choix --}}
                        <div class="col-md-6">
                            <input type="text" id="module-search" class="form-control mb-3" placeholder="Filtrer..." style="display:none;"> <div id="available-modules" class="border rounded p-2 bg-white" style="height: auto; min-height: 200px;">
                                
                                {{-- BOUTON 1 : TOUS LES DOCUMENTS --}}
                                <div class="module-item d-flex align-items-center justify-content-between p-3 mb-3 border rounded bg-white shadow-sm" 
                                     data-id="0" 
                                     data-type="Document" 
                                     data-name="Tous les Documents" 
                                     style="cursor: pointer; transition: 0.2s;">
                                    <div>
                                        <span class="fw-bold d-block text-primary">Gestion des Documents</span>
                                        <small class="text-muted">Alerter si un document obligatoire manque</small>
                                    </div>
                                    <i class="bi bi-file-earmark-text fs-3 text-primary"></i>
                                </div>

                                {{-- BOUTON 2 : TOUS LES ÉVÉNEMENTS --}}
                                <div class="module-item d-flex align-items-center justify-content-between p-3 mb-2 border rounded bg-white shadow-sm" 
                                     data-id="0" 
                                     data-type="Evènement" 
                                     data-name="Tous les Événements" 
                                     style="cursor: pointer; transition: 0.2s;">
                                    <div>
                                        <span class="fw-bold d-block text-success">Gestion des Événements</span>
                                        <small class="text-muted">Rappel automatique avant chaque événement</small>
                                    </div>
                                    <i class="bi bi-calendar-event fs-3 text-success"></i>
                                </div>

                            </div>
                        </div>

                        {{-- Colonne Droite : Le Résultat Sélectionné --}}
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted small">Sélection active*</span>
                            </div>

                            <div id="selected-modules-container" class="border rounded p-3 bg-light" style="height: 200px; display: flex; align-items: center; justify-content: center;">
                                <div class="module-list-empty-message text-muted text-center">
                                    <i class="bi bi-arrow-left me-2"></i> Cliquez sur un module à gauche
                                </div>
                            </div>

                            {{-- Ici seront injectés les inputs cachés pour le Controller --}}
                            <div id="hidden-inputs-container"></div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3 mt-5">
                        <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary px-4 fw-bold">Annuler</a>
                        <button type="submit" class="btn text-white px-4 fw-bold" style="background-color: #F59E0B;">Gorde (Enregistrer)</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // -- STYLE CSS INTERNE --
            const style = document.createElement('style');
            style.innerHTML = `
                .form-check-input:checked {
                    background-color: #F59E0B;
                    border-color: #F59E0B;
                }
                .module-item:hover {
                    background-color: #f8f9fa !important;
                    transform: translateX(5px);
                }
                .module-selected-item {
                    background-color: #F59E0B;
                    color: white;
                    width: 100%;
                }
            `;
            document.head.appendChild(style);

            // -- LOGIQUE JAVASCRIPT --
            const availableContainer = document.getElementById('available-modules');
            const selectedContainer = document.getElementById('selected-modules-container');
            const hiddenInputsContainer = document.getElementById('hidden-inputs-container');
            
            // 1. GESTION DU CLIC (AJOUT)
            availableContainer.addEventListener('click', function(e) {
                const item = e.target.closest('.module-item');
                if (!item) return;

                const id = item.dataset.id;
                const type = item.dataset.type;
                const name = item.dataset.name;

                addModule(id, type, name, item);
            });

            // 2. FONCTION POUR AJOUTER
            function addModule(id, type, name, originalItem) {
                // IMPORTANT : Mode "Single Select" (On vide avant d'ajouter)
                clearSelection();

                // Masquer à gauche
                originalItem.style.display = 'none';
                originalItem.classList.remove('d-flex');

                // Vider le message "Vide"
                selectedContainer.innerHTML = '';
                selectedContainer.style.display = 'block'; // Reset display style

                // Créer l'élément visuel à droite
                const selectedItem = document.createElement('div');
                selectedItem.className = 'module-selected-item d-flex align-items-center justify-content-between p-3 mb-2 border rounded shadow-sm';
                selectedItem.dataset.originId = id; // ID 0
                selectedItem.dataset.originType = type; // Pour retrouver le bon bouton à gauche
                
                selectedItem.innerHTML = `
                    <div>
                        <span class="fw-bold d-block">${name}</span>
                        <small style="opacity: 0.8;">Type: ${type}</small>
                    </div>
                    <i class="bi bi-x-circle-fill fs-4 text-white" style="cursor: pointer;"></i>
                `;

                // Suppression au clic sur la croix
                selectedItem.querySelector('.bi-x-circle-fill').addEventListener('click', function() {
                    removeModule(selectedItem, originalItem);
                });

                selectedContainer.appendChild(selectedItem);

                // --- CRÉATION DES INPUTS POUR LE CONTROLLER ---
                // C'est ce qui permet à $request->module_id de fonctionner
                
                const inputId = document.createElement('input');
                inputId.type = 'hidden';
                inputId.name = 'module_id'; 
                inputId.value = id;
                inputId.className = 'hidden-input-active';

                const inputType = document.createElement('input');
                inputType.type = 'hidden';
                inputType.name = 'module_type';
                inputType.value = type;
                inputType.className = 'hidden-input-active';

                hiddenInputsContainer.appendChild(inputId);
                hiddenInputsContainer.appendChild(inputType);
            }

            // 3. FONCTION POUR RETIRER
            function removeModule(selectedItem, originalItem) {
                selectedItem.remove();

                // Réafficher le bouton à gauche
                originalItem.style.display = null;
                originalItem.classList.add('d-flex');

                // Vider les inputs cachés
                hiddenInputsContainer.innerHTML = '';

                // Remettre le message vide
                selectedContainer.style.display = 'flex';
                selectedContainer.innerHTML = '<div class="module-list-empty-message text-muted text-center"><i class="bi bi-arrow-left me-2"></i> Cliquez sur un module à gauche</div>';
            }

            // 4. FONCTION POUR VIDER (Si on change d'avis)
            function clearSelection() {
                const existingItems = selectedContainer.querySelectorAll('.module-selected-item');
                existingItems.forEach(item => {
                    // On clique virtuellement sur la croix
                    item.querySelector('.bi-x-circle-fill').click();
                });
            }
        });
    </script>
    @endpush
</x-app-layout>

