<x-app-layout>
    <div class="container py-5">
        <a href="{{ route('admin.etiquettes.index') }}"
            class="admin-back-link mb-4 d-inline-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i>
            <span>{{ __('etiquette.retour') }}</span>
        </a>


        <h2 class="mb-4 fw-bold text-center">Nouvelle étiquette</h2>

        <form action="{{ route('admin.etiquettes.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <label for="nom" class="form-label fw-bold">Nom de l'étiquette</label>
            <input type="text" name="nom" id="nom" class="form-control mb-3" value="{{ old('nom') }}"
                required maxlength="50">

            <div class="col-12">
                <div class="form-label fw-semibold mb-2">{{ __('admin.accounts_page.create.fields.roles') }}</div>

                <div class="role-selector-container">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="role-search"
                                class="form-label small">{{ __('admin.accounts_page.create.fields.roles_search') }}</label>
                            <input type="text" id="role-search" class="form-control"
                                placeholder="{{ __('admin.accounts_page.create.fields.roles_search_placeholder') }}">
                            <div id="available-roles" class="role-list mt-2">
                                @foreach ($roles as $role)
                                    <div class="role-item" data-role-id="{{ $role->idRole }}"
                                        data-role-name="{{ $role->name }}">
                                        <span>{{ $role->name }}</span>
                                        <i class="bi bi-plus-circle"></i>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-label small mb-2">
                                {{ __('admin.accounts_page.create.fields.roles_selected') }}
                            </div>
                            <div id="selected-roles" class="role-list mt-2">
                                <div class="role-list-empty-message">Aucun rôle n'a été sélectionné</div>
                            </div>
                            <div id="roles-error" class="invalid-feedback d-none mt-2">La sélection de rôles est
                                optionnelle.</div>
                        </div>
                    </div>

                    <div id="role-inputs">
                        {{-- Les inputs seront ajoutés dynamiquement par JavaScript --}}
                    </div>
                </div>


            </div>




            <div class="d-grid">
                <button type="submit" class="btn btn-primary py-2">Publier l'Étiquette</button>
            </div>

        </form>
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
                const allRolesData = @json(
                    $roles->map(function ($r) {
                            return ['idRole' => $r->idRole, 'name' => $r->name];
                        })->values());
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

                        // Force un reflow seulement si nécessaire
                        if (hasChanges && availableRoles.offsetHeight) {
                            availableRoles.offsetHeight;
                        }
                    });
                }

                // Valider les rôles
                function validateRoles() {
                    // Roles are optional for an étiquette; always valid client-side.
                    rolesError.classList.remove('d-block');
                    rolesError.classList.add('d-none');
                    return true;
                }

                // Créer un élément de rôle disponible (optimisé)
                function createRoleItem(role) {
                    const roleItem = document.createElement('div');
                    roleItem.className = 'role-item';
                    roleItem.dataset.roleId = role.idRole;
                    roleItem.dataset.roleName = role.name;

                    const span = document.createElement('span');
                    span.textContent = role.name;
                    const icon = document.createElement('i');
                    icon.className = 'bi bi-plus-circle';

                    roleItem.appendChild(span);
                    roleItem.appendChild(icon);

                    roleItem.addEventListener('click', function() {
                        addRole(roleItem);
                    });

                    // Ajouter au cache
                    roleNamesCache.set(roleItem, normalizeString(role.name));
                    roleElements.push(roleItem);

                    return roleItem;
                }

                // Debounce pour la recherche de rôles
                let searchTimeout;
                roleSearch.addEventListener('input', function(e) {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        filterRoles(e.target.value);
                    }, 150);
                });

                // Mettre à jour le message vide
                function updateEmptyMessage() {
                    const emptyMessage = selectedRoles.querySelector('.role-list-empty-message');
                    if (selectedRoleIds.size === 0) {
                        if (!emptyMessage) {
                            const message = document.createElement('div');
                            message.className = 'role-list-empty-message';
                            message.textContent = 'Aucun rôle n\'a été sélectionné';
                            selectedRoles.appendChild(message);
                        }
                    } else {
                        if (emptyMessage) {
                            emptyMessage.remove();
                        }
                    }
                }

                // Ajouter un rôle (optimisé avec DocumentFragment)
                function addRole(roleItem) {
                    const roleId = roleItem.dataset.roleId;
                    const roleName = roleItem.dataset.roleName;

                    if (selectedRoleIds.has(roleId)) {
                        return;
                    }

                    selectedRoleIds.add(roleId);

                    // Utiliser requestAnimationFrame pour les mises à jour DOM
                    requestAnimationFrame(() => {
                        // Créer l'élément dans la liste sélectionnée
                        const selectedItem = document.createElement('div');
                        selectedItem.className = 'role-item selected';
                        selectedItem.dataset.roleId = roleId;
                        selectedItem.dataset.roleName = roleName;

                        const span = document.createElement('span');
                        span.textContent = roleName;
                        const icon = document.createElement('i');
                        icon.className = 'bi bi-x-circle';

                        selectedItem.appendChild(span);
                        selectedItem.appendChild(icon);

                        // Ajouter l'événement pour retirer
                        selectedItem.addEventListener('click', function() {
                            removeRole(roleId);
                        });

                        selectedRoles.appendChild(selectedItem);

                        // Créer l'input hidden
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'roles[]';
                        input.value = roleId;
                        roleInputs.appendChild(input);

                        // Masquer le rôle de la liste disponible
                        roleItem.style.display = 'none';

                        // Réinitialiser la recherche et réafficher tous les rôles
                        roleSearch.value = '';

                        // Réafficher tous les rôles non sélectionnés sans recalculer
                        roleElements.forEach(el => {
                            if (!selectedRoleIds.has(el.dataset.roleId)) {
                                el.style.display = 'flex';
                            }
                        });

                        // Mettre à jour le message vide
                        updateEmptyMessage();

                        // Valider les rôles
                        validateRoles();
                    });
                }

                // Retirer un rôle (optimisé)
                function removeRole(roleId) {
                    selectedRoleIds.delete(roleId);

                    requestAnimationFrame(() => {
                        // Retirer de la liste sélectionnée
                        const selectedItem = selectedRoles.querySelector(`[data-role-id="${roleId}"]`);
                        if (selectedItem) {
                            selectedItem.remove();
                        }

                        // Retirer l'input hidden
                        const input = roleInputs.querySelector(`input[value="${roleId}"]`);
                        if (input) {
                            input.remove();
                        }

                        // Réafficher dans la liste disponible
                        let availableItem = roleElements.find(el => el.dataset.roleId == roleId);
                        if (!availableItem) {
                            // Si le rôle n'existe pas dans la liste disponible, le créer
                            const role = allRolesData.find(r => r.idRole == roleId);
                            if (role) {
                                availableItem = createRoleItem(role);
                                roleNamesCache.set(availableItem, normalizeString(role.name));
                                roleElements.push(availableItem);
                                availableRoles.appendChild(availableItem);
                            }
                        } else {
                            availableItem.style.display = 'flex';
                        }

                        // Réappliquer le filtre de recherche seulement si nécessaire
                        if (availableItem && roleSearch.value.trim().length > 0) {
                            filterRoles(roleSearch.value);
                        } else if (availableItem) {
                            availableItem.style.display = 'flex';
                        }

                        // Mettre à jour le message vide
                        updateEmptyMessage();

                        // Valider les rôles
                        validateRoles();
                    });
                }

                // Ajouter les événements aux rôles disponibles
                availableRoles.addEventListener('click', function(e) {
                    const roleItem = e.target.closest('.role-item');
                    if (roleItem && !selectedRoleIds.has(roleItem.dataset.roleId)) {
                        addRole(roleItem);
                    }
                });

                // Ajouter les événements aux rôles sélectionnés
                selectedRoles.addEventListener('click', function(e) {
                    const roleItem = e.target.closest('.role-item');
                    if (roleItem) {
                        removeRole(roleItem.dataset.roleId);
                    }
                });

                // Valider avant la soumission du formulaire
                form.addEventListener('submit', function(e) {
                    if (!validateRoles()) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                });

            });
        </script>
    @endpush
</x-app-layout>
