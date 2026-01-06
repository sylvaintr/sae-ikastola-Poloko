<x-app-layout>
    <div class="container py-4">
        <a href="{{ route('evenements.index') }}" class="admin-back-link mb-4 d-inline-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i>
            <span>Retour aux événements</span>
        </a>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h1 class="h4 fw-bold mb-4">Créer un nouvel événement</h1>

                <form method="POST" action="{{ route('evenements.store') }}" class="admin-form">
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="titre" class="form-label fw-semibold">Titre</label>
                            <input id="titre" name="titre" type="text"
                                   class="form-control @error('titre') is-invalid @enderror"
                                   value="{{ old('titre') }}" required maxlength="255">
                            @error('titre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="dateE" class="form-label fw-semibold">Date</label>
                            <input id="dateE" name="dateE" type="date"
                                   class="form-control @error('dateE') is-invalid @enderror"
                                   value="{{ old('dateE') }}" required>
                            @error('dateE')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label fw-semibold">Description</label>
                            <textarea id="description" name="description"
                                      class="form-control @error('description') is-invalid @enderror"
                                      rows="4" required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Obligatoire (optionnel) --}}
                        <div class="col-md-6">
                            <label for="obligatoire" class="form-label fw-semibold">Obligatoire</label>
                            <div class="form-check form-switch mt-2">
                                <input id="obligatoire" name="obligatoire" type="checkbox"
                                       class="form-check-input" value="1"
                                       {{ old('obligatoire') ? 'checked' : '' }}>
                                <label for="obligatoire" class="form-check-label">Oui, cet événement est obligatoire</label>
                            </div>
                            @error('obligatoire')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                       

                        <div class="col-12">
                            <div class="form-label fw-semibold mb-2">Cibles</div>

                            <div class="role-selector-container">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="role-search" class="form-label small">Rechercher une cible</label>
                                        <input type="text" id="role-search" class="form-control" placeholder="Tapez pour rechercher...">
                                        <div id="available-roles" class="role-list mt-2">
                                            @foreach($roles as $role)
                                                <div class="role-item" data-role-id="{{ $role->idRole }}" data-role-name="{{ $role->name }}">
                                                    <span>{{ $role->name }}</span>
                                                    <i class="bi bi-plus-circle"></i>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-label small mb-2">Cibles sélectionnés</div>
                                        <div id="selected-roles" class="role-list mt-2">
                                            <div class="role-list-empty-message">Aucune cible n'a été sélectionnée</div>
                                        </div>
                                        <div id="roles-error" class="invalid-feedback d-none mt-2">Au moins une cible doit être sélectionnée.</div>
                                    </div>
                                </div>

                                <div id="role-inputs">
                                    {{-- Les inputs seront ajoutés dynamiquement par JavaScript --}}
                                </div>
                            </div>

                            @error('roles')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                            @error('roles.*')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-3 mt-4 justify-content-end">
                        <a href="{{ route('evenements.index') }}" class="btn admin-cancel-btn px-4">
                            Annuler
                        </a>
                        <button type="submit" class="btn fw-semibold px-4 admin-submit-btn">
                            Créer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSearch = document.getElementById('role-search');
            const availableRoles = document.getElementById('available-roles');
            const selectedRoles = document.getElementById('selected-roles');
            const roleInputs = document.getElementById('role-inputs');
            const rolesError = document.getElementById('roles-error');
            const selectedRoleIds = new Set();
            const allRolesData = @json($roles->map(function($r) { return ['idRole' => $r->idRole, 'name' => $r->name]; })->values());
            const form = document.querySelector('form');

            // Normaliser une chaîne en supprimant les accents
            function normalizeString(str) {
                return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
            }

            // Cache pour les noms de rôles normalisés
            const roleNamesCache = new Map();
            const roleElements = Array.from(availableRoles.querySelectorAll('.role-item'));
            roleElements.forEach(role => {
                roleNamesCache.set(role, normalizeString(role.dataset.roleName));
            });

            // Filtrer les rôles disponibles (optimisé avec batch DOM updates)
            function filterRoles(searchTerm) {
                const normalizedTerm = normalizeString(searchTerm.trim());
                const hasTerm = normalizedTerm.length > 0;

                // Utiliser requestAnimationFrame pour de meilleures performances
                requestAnimationFrame(() => {
                    // Batch les modifications DOM
                    let hasChanges = false;

                    roleElements.forEach(role => {
                        const roleId = role.dataset.roleId;
                        const isSelected = selectedRoleIds.has(roleId);

                        if (isSelected) {
                            if (role.style.display !== 'none') {
                                role.style.display = 'none';
                                hasChanges = true;
                            }
                            return;
                        }

                        if (!hasTerm) {
                            if (role.style.display !== 'flex') {
                                role.style.display = 'flex';
                                hasChanges = true;
                            }
                            return;
                        }

                        const normalizedRoleName = roleNamesCache.get(role);
                        const shouldShow = normalizedRoleName.includes(normalizedTerm);
                        const currentDisplay = role.style.display;

                        if (shouldShow && currentDisplay !== 'flex') {
                            role.style.display = 'flex';
                            hasChanges = true;
                        } else if (!shouldShow && currentDisplay !== 'none') {
                            role.style.display = 'none';
                            hasChanges = true;
                        }
                    });

                    // Forcer un reflow si nécessaire
                    if (hasChanges) {
                        availableRoles.offsetHeight;
                    }
                });
            }

            // Créer un élément de rôle sélectionné
            function createSelectedRoleElement(roleId, roleName) {
                const roleElement = document.createElement('div');
                roleElement.className = 'role-item selected';
                roleElement.dataset.roleId = roleId;
                roleElement.dataset.roleName = roleName;
                roleElement.innerHTML = `
                    <span>${roleName}</span>
                    <i class="bi bi-dash-circle"></i>
                `;

                roleElement.addEventListener('click', function() {
                    removeRole(roleId);
                });

                return roleElement;
            }

            // Ajouter un rôle à la sélection
            function addRole(roleId) {
                if (selectedRoleIds.has(roleId)) return;

                selectedRoleIds.add(roleId);
                const roleData = allRolesData.find(r => r.idRole == roleId);
                if (!roleData) return;

                // Masquer le message vide
                const emptyMessage = selectedRoles.querySelector('.role-list-empty-message');
                if (emptyMessage) {
                    emptyMessage.style.display = 'none';
                }

                // Ajouter l'élément sélectionné
                const selectedRoleElement = createSelectedRoleElement(roleId, roleData.name);
                selectedRoles.appendChild(selectedRoleElement);

                // Ajouter l'input caché
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'roles[]';
                input.value = roleId;
                roleInputs.appendChild(input);

                // Mettre à jour l'affichage des rôles disponibles
                updateAvailableRolesDisplay();

                // Cacher l'erreur si elle était affichée
                if (rolesError) {
                    rolesError.classList.add('d-none');
                }
            }

            // Retirer un rôle de la sélection
            function removeRole(roleId) {
                if (!selectedRoleIds.has(roleId)) return;

                selectedRoleIds.delete(roleId);

                // Supprimer l'élément sélectionné
                const selectedRoleElement = selectedRoles.querySelector(`[data-role-id="${roleId}"]`);
                if (selectedRoleElement) {
                    selectedRoleElement.remove();
                }

                // Supprimer l'input caché
                const input = roleInputs.querySelector(`input[value="${roleId}"]`);
                if (input) {
                    input.remove();
                }

                // Afficher le message vide si aucun rôle n'est sélectionné
                if (selectedRoleIds.size === 0) {
                    const emptyMessage = document.createElement('div');
                    emptyMessage.className = 'role-list-empty-message';
                    emptyMessage.textContent = 'Aucun rôle n\'a été sélectionné';
                    selectedRoles.appendChild(emptyMessage);
                }

                // Mettre à jour l'affichage des rôles disponibles
                updateAvailableRolesDisplay();
            }

            // Mettre à jour l'affichage des rôles disponibles
            function updateAvailableRolesDisplay() {
                roleElements.forEach(role => {
                    const roleId = role.dataset.roleId;
                    const isSelected = selectedRoleIds.has(roleId);

                    if (isSelected) {
                        role.style.display = 'none';
                    } else {
                        // Respecter le filtre de recherche actuel
                        const searchTerm = roleSearch.value.trim();
                        if (searchTerm) {
                            const normalizedTerm = normalizeString(searchTerm);
                            const normalizedRoleName = roleNamesCache.get(role);
                            role.style.display = normalizedRoleName.includes(normalizedTerm) ? 'flex' : 'none';
                        } else {
                            role.style.display = 'flex';
                        }
                    }
                });
            }

            // Gestionnaire d'événement pour la recherche
            roleSearch.addEventListener('input', function() {
                filterRoles(this.value);
            });

            // Gestionnaire d'événement pour les rôles disponibles
            availableRoles.addEventListener('click', function(e) {
                const roleItem = e.target.closest('.role-item');
                if (roleItem && !roleItem.classList.contains('selected')) {
                    const roleId = roleItem.dataset.roleId;
                    addRole(roleId);
                }
            });

            // Validation du formulaire
            form.addEventListener('submit', function(e) {
                if (selectedRoleIds.size === 0) {
                    e.preventDefault();
                    rolesError.classList.remove('d-none');
                    rolesError.textContent = 'Au moins un rôle doit être sélectionné.';

                    // Scroll vers l'erreur
                    rolesError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return false;
                }
            });

            // Initialisation : restaurer les valeurs old() si elles existent
            const oldRoles = @json(old('roles', []));
            if (Array.isArray(oldRoles) && oldRoles.length > 0) {
                oldRoles.forEach(roleId => {
                    addRole(roleId);
                });
            }
        });
    </script>
    @endpush

</x-app-layout>
